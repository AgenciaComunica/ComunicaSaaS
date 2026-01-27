<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\MetaMonthly;
use App\Models\UploadBatch;
use Illuminate\Support\Collection;

class MetricsCalculatorService
{
    public function calculateForBatches(Collection $batches, string $originFilter = 'TRAFEGO_PAGO'): array
    {
        $batches = $batches->sortBy(fn (UploadBatch $batch) => sprintf('%04d%02d', $batch->year, $batch->month));
        $monthKeys = $batches->map(fn (UploadBatch $batch) => $this->monthKey($batch->year, $batch->month))->values();

        $metaRows = MetaMonthly::whereIn('upload_batch_id', $batches->pluck('id'))->get();
        $leads = Lead::whereIn('upload_batch_id', $batches->pluck('id'))->get();

        $metaByMonth = [];
        foreach ($metaRows as $meta) {
            $key = $this->monthKey($meta->year, $meta->month);
            $metaByMonth[$key] = [
                'spend' => (float) $meta->spend,
                'impressions' => (int) $meta->impressions,
                'clicks' => (int) $meta->clicks,
                'ctr' => (float) $meta->ctr,
                'cpc' => (float) $meta->cpc,
                'leads' => (int) $meta->leads,
                'results' => (int) ($meta->results ?? 0),
            ];
        }

        $metaTotal = $this->sumMeta($metaByMonth);

        $originFilter = $this->normalizeOriginFilter($originFilter);
        $funnelSelected = $this->calculateFunnel($leads, $originFilter === 'ALL' ? null : $originFilter);

        $roi = $this->calculateRoi($funnelSelected, $metaByMonth, $originFilter);
        $custoTemperatura = $this->calculateCostByTemperature($funnelSelected, $metaByMonth);

        $roiSummary = $this->buildRoiSummary($roi, $monthKeys);

        return [
            'months' => $monthKeys,
            'origin_filter' => $originFilter,
            'meta' => [
                'by_month' => $metaByMonth,
                'total' => $metaTotal,
            ],
            'funnel_selected' => $funnelSelected,
            'roi' => $roi,
            'custo_temperatura' => $custoTemperatura,
            'roi_summary' => $roiSummary,
        ];
    }

    private function calculateFunnel(Collection $leads, ?string $origin): array
    {
        $filtered = $origin ? $leads->where('origin', $origin) : $leads;
        $grouped = $filtered->groupBy(fn (Lead $lead) => $this->monthKey($lead->year, $lead->month));

        $byMonth = [];
        foreach ($grouped as $month => $items) {
            $byMonth[$month] = $this->funnelFromLeads($items);
        }

        $total = $this->funnelFromLeads($filtered);

        return [
            'by_month' => $byMonth,
            'total' => $total,
        ];
    }

    private function funnelFromLeads(Collection $leads): array
    {
        $total = $leads->count();
        $byTemp = [
            'FRIO' => $leads->where('temperature', 'FRIO')->count(),
            'QUENTE' => $leads->where('temperature', 'QUENTE')->count(),
            'MUITO_QUENTE' => $leads->where('temperature', 'MUITO_QUENTE')->count(),
            'SEM_TEMPERATURA' => $leads->where('temperature', 'SEM_TEMPERATURA')->count(),
        ];

        $vendas = $leads->where('venda_concluida', true)->count();
        $receita = $leads->where('venda_concluida', true)->sum('valor_venda');

        return [
            'leads' => $total,
            'temperaturas' => $byTemp,
            'percentuais' => $this->percentages($byTemp, $total),
            'vendas' => $vendas,
            'receita' => (float) $receita,
            'taxa_conversao' => $total > 0 ? $vendas / $total : null,
            'ticket_medio' => $vendas > 0 ? $receita / $vendas : null,
        ];
    }

    private function calculateRoi(array $funnelSelected, array $metaByMonth, string $originFilter): array
    {
        if ($originFilter === 'ORGANICO') {
            return [
                'by_month' => [],
                'total' => ['roi' => null, 'roas' => null],
            ];
        }

        $byMonth = [];
        foreach ($funnelSelected['by_month'] as $month => $data) {
            $invest = $metaByMonth[$month]['spend'] ?? 0;
            $receita = $data['receita'] ?? 0;
            $byMonth[$month] = $this->roiMetrics($invest, $receita);
        }

        $investTotal = 0;
        foreach ($metaByMonth as $row) {
            $investTotal += $row['spend'];
        }
        $total = $this->roiMetrics($investTotal, $funnelSelected['total']['receita'] ?? 0);

        return [
            'by_month' => $byMonth,
            'total' => $total,
        ];
    }

    private function calculateCostByTemperature(array $funnelSelected, array $metaByMonth): array
    {
        $byMonth = [];
        foreach ($funnelSelected['by_month'] as $month => $data) {
            $invest = $metaByMonth[$month]['spend'] ?? 0;
            $byMonth[$month] = $this->costByTemp($invest, $data['temperaturas'] ?? []);
        }

        $total = $this->costByTemp(
            array_sum(array_column($metaByMonth, 'spend')),
            $funnelSelected['total']['temperaturas'] ?? []
        );

        return [
            'by_month' => $byMonth,
            'total' => $total,
        ];
    }

    private function roiMetrics(float $invest, float $receita): array
    {
        if ($invest <= 0) {
            return ['roi' => null, 'roas' => null];
        }

        return [
            'roi' => ($receita - $invest) / $invest,
            'roas' => $receita / $invest,
        ];
    }

    private function costByTemp(float $invest, array $temps): array
    {
        return [
            'FRIO' => ($temps['FRIO'] ?? 0) > 0 ? $invest / $temps['FRIO'] : null,
            'QUENTE' => ($temps['QUENTE'] ?? 0) > 0 ? $invest / $temps['QUENTE'] : null,
            'MUITO_QUENTE' => ($temps['MUITO_QUENTE'] ?? 0) > 0 ? $invest / $temps['MUITO_QUENTE'] : null,
            'SEM_TEMPERATURA' => ($temps['SEM_TEMPERATURA'] ?? 0) > 0 ? $invest / $temps['SEM_TEMPERATURA'] : null,
        ];
    }

    private function percentages(array $temps, int $total): array
    {
        if ($total === 0) {
            return [
                'FRIO' => null,
                'QUENTE' => null,
                'MUITO_QUENTE' => null,
                'SEM_TEMPERATURA' => null,
            ];
        }

        return [
            'FRIO' => $temps['FRIO'] / $total,
            'QUENTE' => $temps['QUENTE'] / $total,
            'MUITO_QUENTE' => $temps['MUITO_QUENTE'] / $total,
            'SEM_TEMPERATURA' => $temps['SEM_TEMPERATURA'] / $total,
        ];
    }

    private function sumMeta(array $byMonth): array
    {
        $total = [
            'spend' => 0.0,
            'impressions' => 0,
            'clicks' => 0,
            'ctr' => 0.0,
            'cpc' => 0.0,
            'leads' => 0,
            'results' => 0,
        ];

        foreach ($byMonth as $row) {
            $total['spend'] += $row['spend'];
            $total['impressions'] += $row['impressions'];
            $total['clicks'] += $row['clicks'];
            $total['leads'] += $row['leads'];
            $total['results'] += $row['results'] ?? 0;
        }

        if ($total['impressions'] > 0) {
            $total['ctr'] = $total['clicks'] / $total['impressions'];
        }
        if ($total['clicks'] > 0) {
            $total['cpc'] = $total['spend'] / $total['clicks'];
        }

        return $total;
    }

    private function buildRoiSummary(array $roi, Collection $months): array
    {
        $current = $months->last();
        $previous = $months->count() >= 2 ? $months->get($months->count() - 2) : null;

        $currentRoi = $current ? ($roi['by_month'][$current]['roi'] ?? null) : null;
        $currentRoas = $current ? ($roi['by_month'][$current]['roas'] ?? null) : null;
        $prevRoi = $previous ? ($roi['by_month'][$previous]['roi'] ?? null) : null;
        $prevRoas = $previous ? ($roi['by_month'][$previous]['roas'] ?? null) : null;

        return [
            'current_month' => $current,
            'previous_month' => $previous,
            'roi' => $currentRoi,
            'roas' => $currentRoas,
            'roi_delta' => $this->delta($currentRoi, $prevRoi),
            'roas_delta' => $this->delta($currentRoas, $prevRoas),
        ];
    }

    private function monthKey(int $year, int $month): string
    {
        return sprintf('%04d-%02d', $year, $month);
    }

    private function delta(?float $current, ?float $previous): ?float
    {
        if (is_null($current) || is_null($previous) || $previous == 0.0) {
            return null;
        }

        return ($current - $previous) / abs($previous);
    }

    private function normalizeOriginFilter(string $originFilter): string
    {
        $originFilter = strtoupper(trim($originFilter));
        if (in_array($originFilter, ['TRAFEGO_PAGO', 'ORGANICO', 'ALL'], true)) {
            return $originFilter;
        }

        return 'TRAFEGO_PAGO';
    }
}

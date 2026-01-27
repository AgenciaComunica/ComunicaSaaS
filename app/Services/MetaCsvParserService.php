<?php

namespace App\Services;

use App\Models\MetaMonthly;
use App\Models\UploadBatch;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

class MetaCsvParserService
{
    public function parse(string $path, UploadBatch $batch): array
    {
        $reader = Reader::createFromPath($path);
        $reader->setDelimiter($this->detectDelimiter($path));
        $reader->setHeaderOffset(0);

        $headers = $reader->getHeader();
        $normalized = [];
        foreach ($headers as $header) {
            $normalized[$header] = ColumnNormalizer::normalize($header);
        }

        $map = $this->mapColumns($normalized);
        if (!$map['spend']) {
            throw new \RuntimeException('Coluna de investimento/gasto não encontrada no CSV Meta Ads.');
        }

        $totals = [
            'spend' => 0.0,
            'impressions' => 0,
            'clicks' => 0,
            'ctr' => 0.0,
            'cpc' => 0.0,
            'leads' => 0,
            'results' => 0,
        ];

        $rowCount = 0;
        foreach ($reader->getRecords() as $record) {
            $rowCount++;
            $totals['spend'] += $this->parseNumber($this->valueFromMap($record, $map['spend']));
            $totals['impressions'] += (int) $this->parseNumber($this->valueFromMap($record, $map['impressions']));
            $totals['clicks'] += (int) $this->parseNumber($this->valueFromMap($record, $map['clicks']));
            $totals['leads'] += (int) $this->parseNumber($this->valueFromMap($record, $map['leads']));
            $totals['results'] += (int) $this->parseNumber($this->valueFromMap($record, $map['results']));
        }

        if ($totals['impressions'] > 0) {
            $totals['ctr'] = $totals['clicks'] / $totals['impressions'];
        }
        if ($totals['clicks'] > 0) {
            $totals['cpc'] = $totals['spend'] / $totals['clicks'];
        }

        MetaMonthly::updateOrCreate(
            [
                'upload_batch_id' => $batch->id,
                'year' => $batch->year,
                'month' => $batch->month,
            ],
            [
                'spend' => $totals['spend'],
                'impressions' => $totals['impressions'],
                'clicks' => $totals['clicks'],
                'ctr' => $totals['ctr'],
                'cpc' => $totals['cpc'],
                'leads' => $totals['leads'],
                'results' => $totals['results'],
                'raw_totals' => $totals,
            ]
        );

        $stats = [
            'rows' => $rowCount,
            'spend' => $totals['spend'],
            'impressions' => $totals['impressions'],
            'clicks' => $totals['clicks'],
            'leads' => $totals['leads'],
            'results' => $totals['results'],
        ];

        Log::info('Meta CSV parsed', ['batch_id' => $batch->id, 'stats' => $stats]);

        return $stats;
    }

    private function mapColumns(array $normalizedHeaders): array
    {
        return [
            'spend' => ColumnNormalizer::findBySynonyms($normalizedHeaders, [
                'amount spent',
                'valor gasto',
                'valor usado',
                'gasto',
                'spend',
                'investimento',
                'valor investido',
                'spent',
            ]),
            'impressions' => ColumnNormalizer::findBySynonyms($normalizedHeaders, [
                'impressoes',
                'impressions',
                'impressao',
            ]),
            'clicks' => ColumnNormalizer::findBySynonyms($normalizedHeaders, [
                'cliques',
                'cliques no link',
                'clicks',
                'clique',
            ]),
            'ctr' => ColumnNormalizer::findBySynonyms($normalizedHeaders, [
                'ctr',
                'ctr todos',
                'taxa de cliques',
                'click through',
            ]),
            'cpc' => ColumnNormalizer::findBySynonyms($normalizedHeaders, [
                'cpc',
                'cpc custo por clique no link',
                'custo por clique',
            ]),
            'leads' => ColumnNormalizer::findBySynonyms($normalizedHeaders, [
                'leads',
                'lead',
                'mensagens',
                'mensagem',
                'conversas',
                'conversa',
            ]),
            'results' => $this->findResultsColumn($normalizedHeaders),
        ];
    }

    private function parseNumber($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = (string) $value;
        $value = preg_replace('/[^0-9,.-]/', '', $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }

    private function valueFromMap(array $record, ?string $key)
    {
        if (!$key) {
            return 0;
        }

        return $record[$key] ?? 0;
    }

    private function findResultsColumn(array $normalizedHeaders): ?string
    {
        foreach ($normalizedHeaders as $original => $normalized) {
            if (str_contains($normalized, 'tipo de resultado')) {
                continue;
            }
            if ($normalized === 'resultados' || $normalized === 'resultado' || str_contains($normalized, 'resultados')) {
                return $original;
            }
        }

        return ColumnNormalizer::findBySynonyms($normalizedHeaders, [
            'resultados',
            'results',
        ]);
    }

    private function detectDelimiter(string $path): string
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return ',';
        }
        $line = fgets($handle) ?: '';
        fclose($handle);

        $comma = substr_count($line, ',');
        $semicolon = substr_count($line, ';');

        return $semicolon > $comma ? ';' : ',';
    }
}

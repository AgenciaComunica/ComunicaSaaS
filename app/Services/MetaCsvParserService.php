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
            $clean = $this->cleanHeader((string) $header);
            $normalized[$header] = ColumnNormalizer::normalize($clean);
        }

        $map = $this->mapColumns($normalized);

        $missing = $this->missingColumns($map);

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
            'missing' => $missing,
        ];

        Log::info('Meta CSV parsed', ['batch_id' => $batch->id, 'stats' => $stats]);

        return $stats;
    }


    private function resolveColumn(array $normalizedHeaders, array $synonyms, array $keywords = [], int $minScore = 1): ?string
    {
        $found = ColumnNormalizer::findBySynonyms($normalizedHeaders, $synonyms);
        if ($found) {
            return $found;
        }

        if ($keywords) {
            return ColumnNormalizer::findBestMatch($normalizedHeaders, $keywords, $minScore);
        }

        return null;
    }

    private function cleanHeader(string $header): string
    {
        return ltrim($header, "\xEF\xBB\xBF");
    }

    private function mapColumns(array $normalizedHeaders): array
    {
        return [
            'spend' => $this->resolveColumn($normalizedHeaders, [
                'amount spent',
                'valor gasto',
                'valor usado',
                'valor usado brl',
                'valor gasto brl',
                'gasto',
                'spend',
                'investimento',
                'valor investido',
                'spent',
            ], ['valor', 'gasto', 'invest', 'spend', 'spent', 'usado'], 1),
            'impressions' => $this->resolveColumn($normalizedHeaders, [
                'impressoes',
                'impressions',
                'impressao',
            ], ['impress', 'impressions'], 1),
            'clicks' => $this->resolveColumn($normalizedHeaders, [
                'cliques',
                'cliques no link',
                'clicks',
                'clique',
            ], ['clique', 'click'], 1),
            'ctr' => $this->resolveColumn($normalizedHeaders, [
                'ctr',
                'ctr todos',
                'taxa de cliques',
                'click through',
            ], ['ctr'], 1),
            'cpc' => $this->resolveColumn($normalizedHeaders, [
                'cpc',
                'cpc custo por clique no link',
                'custo por clique',
            ], ['cpc', 'custo', 'clique'], 2),
            'leads' => $this->resolveColumn($normalizedHeaders, [
                'leads',
                'lead',
                'mensagens',
                'mensagem',
                'conversas',
                'conversa',
                'conversas iniciadas',
            ], ['lead', 'mensagem', 'conversa'], 1),
            'results' => $this->findResultsColumn($normalizedHeaders),
        ];
    }

    private function missingColumns(array $map): array
    {
        $labels = ['spend', 'impressions', 'clicks', 'ctr', 'cpc', 'leads', 'results'];
        $missing = [];
        foreach ($labels as $key) {
            if (empty($map[$key])) {
                $missing[] = $key;
            }
        }

        return $missing;
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

        $found = ColumnNormalizer::findBySynonyms($normalizedHeaders, [
            'resultados',
            'results',
        ]);
        if ($found) {
            return $found;
        }

        $filtered = [];
        foreach ($normalizedHeaders as $original => $normalized) {
            if (!str_contains($normalized, 'tipo de resultado')) {
                $filtered[$original] = $normalized;
            }
        }

        return ColumnNormalizer::findBestMatch($filtered, ['resultado', 'result', 'conversa', 'mensagem'], 1);
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

<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\UploadBatch;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class IntelbrasXlsxParserService
{
    public function parse(string $path, UploadBatch $batch): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            throw new \RuntimeException('Planilha CRM Vendas sem dados.');
        }

        $headerRow = array_shift($rows);
        $normalized = [];
        foreach ($headerRow as $col => $header) {
            $clean = $this->cleanHeader((string) $header);
            $normalized[$col] = ColumnNormalizer::normalize($clean);
        }

        $map = $this->mapColumns($normalized);
        $missing = $this->missingColumns($map);
        $hasFirstMessage = !empty($map['first_message']);

        $stats = [
            'rows' => 0,
            'pago' => 0,
            'organico' => 0,
            'indefinido' => 0,
            'sem_temperatura_pago' => 0,
            'missing' => $missing,
        ];

        foreach ($rows as $row) {
            $firstMessage = trim((string) $this->valueFromMap($row, $map['first_message']));
            $name = trim((string) $this->valueFromMap($row, $map['name']));
            $phone = trim((string) $this->valueFromMap($row, $map['phone']));
            $email = trim((string) $this->valueFromMap($row, $map['email']));
            $temperatureRaw = trim((string) $this->valueFromMap($row, $map['temperature']));
            $valorVendaRaw = $this->valueFromMap($row, $map['valor_venda']);

            if ($firstMessage === '' && $name === '' && $phone === '' && $email === '') {
                continue;
            }

            $stats['rows']++;
            $origin = $hasFirstMessage ? $this->detectOrigin($firstMessage) : 'INDEFINIDO';
            if ($origin === 'TRAFEGO_PAGO') {
                $stats['pago']++;
            } elseif ($origin === 'ORGANICO') {
                $stats['organico']++;
            } else {
                $stats['indefinido']++;
            }

            $temperature = $this->normalizeTemperature($temperatureRaw);
            if ($origin === 'TRAFEGO_PAGO' && $temperature === 'SEM_TEMPERATURA') {
                $stats['sem_temperatura_pago']++;
            }

            $valorVenda = $this->parseNumber($valorVendaRaw);
            $vendaConcluida = $valorVenda > 0;

            Lead::create([
                'upload_batch_id' => $batch->id,
                'year' => $batch->year,
                'month' => $batch->month,
                'name' => $name ?: null,
                'phone' => $phone ?: null,
                'email' => $email ?: null,
                'first_message' => $firstMessage ?: null,
                'origin' => $origin,
                'temperature' => $temperature,
                'valor_venda' => $valorVenda,
                'venda_concluida' => $vendaConcluida,
                'raw' => $row,
            ]);
        }

        Log::info('CRM Vendas XLSX parsed', ['batch_id' => $batch->id, 'stats' => $stats]);

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
            'first_message' => $this->resolveColumn($normalizedHeaders, [
                '1 mensagem',
                '1a mensagem',
                '1o mensagem',
                'primeira mensagem',
                'primeira msg',
                'mensagem inicial',
                'primeiro contato',
                'primeiro atendimento',
                'mensagem do lead',
            ], ['mensagem', 'primeira', 'primeiro', 'contato'], 2),
            'temperature' => $this->resolveColumn($normalizedHeaders, [
                'temperatura',
                'tag',
                'etiqueta',
                'etiquetas',
                'tags',
                'classificacao',
                'classificacao lead',
                'categoria',
            ], ['temperatura', 'tag', 'etiqueta', 'classificacao', 'categoria'], 1),
            'valor_venda' => $this->resolveColumn($normalizedHeaders, [
                'valor venda',
                'valor da venda',
                'valor de venda',
                'venda valor',
                'valor fechamento',
                'valor de fechamento',
                'valor negociado',
                'valor total',
            ], ['valor', 'venda', 'fechamento'], 2),
            'name' => $this->resolveColumn($normalizedHeaders, [
                'nome',
                'cliente',
                'contato',
                'lead',
            ], ['nome', 'cliente', 'contato', 'lead'], 1),
            'phone' => $this->resolveColumn($normalizedHeaders, [
                'telefone',
                'celular',
                'whatsapp',
                'fone',
                'phone',
                'tel',
            ], ['telefone', 'celular', 'whatsapp', 'fone', 'tel', 'phone'], 1),
            'email' => $this->resolveColumn($normalizedHeaders, [
                'email',
                'e mail',
            ], ['email', 'mail'], 1),
        ];
    }

    private function detectOrigin(string $firstMessage): string
    {
        $lower = strtolower($firstMessage);
        if (str_contains($lower, 'http://') || str_contains($lower, 'https://') || str_contains($lower, 'http')) {
            return 'TRAFEGO_PAGO';
        }

        return 'ORGANICO';
    }

    private function normalizeTemperature(string $value): string
    {
        $normalized = ColumnNormalizer::normalize($value);
        if ($normalized === '') {
            return 'SEM_TEMPERATURA';
        }

        // Se houver múltiplas tags, escolhe a de maior nível.
        if (str_contains($normalized, 'cliente')) {
            return 'MUITO_QUENTE';
        }
        if (str_contains($normalized, 'muito') && str_contains($normalized, 'quente')) {
            return 'MUITO_QUENTE';
        }
        if (str_contains($normalized, 'quente')) {
            return 'QUENTE';
        }
        if (str_contains($normalized, 'frio')) {
            return 'FRIO';
        }

        return 'SEM_TEMPERATURA';
    }

    private function missingColumns(array $map): array
    {
        $labels = ['first_message', 'temperature', 'valor_venda', 'name', 'phone', 'email'];
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

    private function valueFromMap(array $row, ?string $key)
    {
        if (!$key) {
            return '';
        }

        return $row[$key] ?? '';
    }
}

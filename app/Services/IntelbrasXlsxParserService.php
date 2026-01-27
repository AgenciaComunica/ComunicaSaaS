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
            throw new \RuntimeException('Planilha Intelbras sem dados.');
        }

        $headerRow = array_shift($rows);
        $normalized = [];
        foreach ($headerRow as $col => $header) {
            $normalized[$col] = ColumnNormalizer::normalize((string) $header);
        }

        $map = $this->mapColumns($normalized);
        if (!$map['first_message']) {
            throw new \RuntimeException('Coluna "1º Mensagem" não encontrada na planilha Intelbras.');
        }

        $stats = [
            'rows' => 0,
            'pago' => 0,
            'organico' => 0,
            'sem_temperatura_pago' => 0,
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
            $origin = $this->detectOrigin($firstMessage);
            if ($origin === 'TRAFEGO_PAGO') {
                $stats['pago']++;
            } else {
                $stats['organico']++;
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

        Log::info('Intelbras XLSX parsed', ['batch_id' => $batch->id, 'stats' => $stats]);

        return $stats;
    }

    private function mapColumns(array $normalizedHeaders): array
    {
        return [
            'first_message' => ColumnNormalizer::findBySynonyms($normalizedHeaders, [
                '1 mensagem',
                '1a mensagem',
                '1o mensagem',
                'primeira mensagem',
                'primeiro contato',
            ]),
            'temperature' => ColumnNormalizer::findBySynonyms($normalizedHeaders, [
                'temperatura',
                'tag',
                'etiqueta',
                'etiquetas',
                'tags',
                'classificacao',
                'classificacao lead',
            ]),
            'valor_venda' => ColumnNormalizer::findBySynonyms($normalizedHeaders, [
                'valor venda',
                'valor da venda',
                'valor de venda',
                'venda valor',
                'valor fechamento',
            ]),
            'name' => ColumnNormalizer::findBySynonyms($normalizedHeaders, [
                'nome',
                'cliente',
                'contato',
                'lead',
            ]),
            'phone' => ColumnNormalizer::findBySynonyms($normalizedHeaders, [
                'telefone',
                'celular',
                'whatsapp',
                'fone',
            ]),
            'email' => ColumnNormalizer::findBySynonyms($normalizedHeaders, [
                'email',
                'e mail',
            ]),
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

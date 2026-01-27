<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\UploadBatch;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RemarketingExporterService
{
    public function export(UploadBatch $batch, array $filters = []): StreamedResponse
    {
        $query = Lead::query()
            ->where('upload_batch_id', $batch->id)
            ->whereIn('temperature', ['QUENTE', 'MUITO_QUENTE']);

        if (!empty($filters['origem'])) {
            $query->where('origin', $filters['origem']);
        }
        if (!empty($filters['mes'])) {
            $query->where('month', (int) $filters['mes']);
        }

        $leads = $query->orderBy('temperature')->get();

        $response = new StreamedResponse(function () use ($leads) {
            $csv = Writer::createFromString('');
            $csv->insertOne([
                'Nome',
                'Telefone/WhatsApp',
                'Email',
                'Mes',
                'Origem',
                'Temperatura',
                'Valor Venda',
            ]);

            foreach ($leads as $lead) {
                $csv->insertOne([
                    $lead->name,
                    $lead->phone,
                    $lead->email,
                    sprintf('%04d-%02d', $lead->year, $lead->month),
                    $lead->origin,
                    $lead->temperature,
                    $lead->valor_venda,
                ]);
            }

            echo $csv->toString();
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="remarketing.csv"');

        return $response;
    }
}

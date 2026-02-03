<?php

namespace App\Jobs;

use App\Models\UploadBatch;
use App\Services\IntelbrasXlsxParserService;
use App\Services\MetaCsvParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessUploadBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $batchId)
    {
    }

    public function handle(MetaCsvParserService $metaParser, IntelbrasXlsxParserService $crmParser): void
    {
        $batch = UploadBatch::find($this->batchId);
        if (!$batch) {
            return;
        }

        $batch->update([
            'status' => 'processing',
            'progress' => 10,
            'error_message' => null,
        ]);

        try {
            DB::transaction(function () use ($batch, $metaParser, $crmParser) {
                $metaPath = $batch->meta_csv_path ? Storage::path($batch->meta_csv_path) : null;
                $crmPath = $batch->intelbras_xlsx_path ? Storage::path($batch->intelbras_xlsx_path) : null;

                $metaStats = $metaPath ? $metaParser->parse($metaPath, $batch) : [];
                $batch->update(['progress' => 60]);

                $crmStats = $crmPath ? $crmParser->parse($crmPath, $batch) : [];
                $batch->update(['progress' => 95]);

                $batch->update([
                    'parse_stats' => [
                        'meta' => $metaStats,
                        'intelbras' => $crmStats,
                    ],
                    'parsed_at' => now(),
                    'status' => 'done',
                    'progress' => 100,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Erro ao processar batch', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);

            $batch->update([
                'status' => 'failed',
                'progress' => 100,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}

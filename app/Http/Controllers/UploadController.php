<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadBatchRequest;
use App\Models\UploadBatch;
use App\Services\IntelbrasXlsxParserService;
use App\Services\MetaCsvParserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Carbon\Carbon;

class UploadController extends Controller
{

    private function scopedBatches()
    {
        $query = UploadBatch::query();
        $user = request()->user();
        if ($user && !$user->isAdmin()) {
            $query->where('created_by', $user->id);
        }
        return $query;
    }

    private function ensureBatchAccess(UploadBatch $batch): void
    {
        $user = request()->user();
        if ($user && !$user->isAdmin() && $batch->created_by !== $user->id) {
            abort(403);
        }
    }
    public function index(): View
    {
        return view('uploads.index', [
            'batches' => $this->scopedBatches()->orderByDesc('year')->orderByDesc('month')->get(),
        ]);
    }

    public function create(): View
    {
        return view('uploads.create');
    }

    public function store(
        UploadBatchRequest $request,
        MetaCsvParserService $metaParser,
        IntelbrasXlsxParserService $intelbrasParser
    ): RedirectResponse {
        $data = $request->validated();
        [$year, $month] = array_map('intval', explode('-', $data['period']));
        $forceReplace = $request->boolean('force_replace');

        $existing = $this->scopedBatches()
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($existing && !$forceReplace) {
            return back()
                ->withInput()
                ->with('confirm_replace', [
                    'label' => $existing->display_label,
                ])
                ->withErrors(['period' => 'Já existe um relatório para este período.']);
        }

        try {
            return DB::transaction(function () use ($data, $year, $month, $request, $metaParser, $intelbrasParser, $existing) {
                if ($existing) {
                    $basePathExisting = "uploads/batches/{$existing->id}";
                    Storage::deleteDirectory($basePathExisting);
                    $existing->delete();
                }

                $label = sprintf(
                    'Relatorio Marketing %s %d',
                    Carbon::create($year, $month, 1)->locale('pt_BR')->translatedFormat('F'),
                    $year
                );
                $batch = UploadBatch::create([
                    'year' => $year,
                    'month' => $month,
                    'label' => $label,
                    'created_by' => $request->user()->id,
                ]);

                $basePath = "uploads/batches/{$batch->id}";
                $metaPath = $request->file('meta_csv')->storeAs($basePath, 'meta.csv');
                $intelbrasPath = $request->file('intelbras_xlsx')->storeAs($basePath, 'intelbras.xlsx');

                $batch->update([
                    'meta_csv_path' => $metaPath,
                    'intelbras_xlsx_path' => $intelbrasPath,
                ]);

                $metaStats = $metaParser->parse(Storage::path($metaPath), $batch);
                $intelbrasStats = $intelbrasParser->parse(Storage::path($intelbrasPath), $batch);

                $batch->update([
                    'parse_stats' => [
                        'meta' => $metaStats,
                        'intelbras' => $intelbrasStats,
                    ],
                    'parsed_at' => now(),
                ]);

                return redirect()->route('uploads.index')->with('success', 'Upload processado com sucesso.');
            });
        } catch (\Throwable $e) {
            Log::error('Erro ao processar batch', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['upload' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(UploadBatch $batch): RedirectResponse
    {
        $this->ensureBatchAccess($batch);
        $basePath = "uploads/batches/{$batch->id}";

        try {
            Storage::deleteDirectory($basePath);
            $batch->delete();
        } catch (\Throwable $e) {
            Log::error('Erro ao excluir batch', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['delete' => 'Não foi possível excluir o relatório.']);
        }

        return redirect()->route('uploads.index')->with('success', 'Relatório excluído com sucesso.');
    }
}

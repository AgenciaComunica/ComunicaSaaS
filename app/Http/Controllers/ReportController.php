<?php

namespace App\Http\Controllers;

use App\Models\UploadBatch;
use App\Services\MetricsCalculatorService;
use App\Services\PdfReportGeneratorService;
use App\Services\RemarketingExporterService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
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
    public function index(): View|RedirectResponse
    {
        $latest = $this->scopedBatches()->orderByDesc('year')->orderByDesc('month')->first();
        if (!$latest) {
            return view('reports.empty');
        }

        return redirect()->route('reports.show', $latest);
    }

    public function show(UploadBatch $batch, Request $request, MetricsCalculatorService $metricsService): View
    {
        $this->ensureBatchAccess($batch);
        $batches = $this->resolveBatches($batch, $request);
        $metrics = $metricsService->calculateForBatches($batches, $request->query('origem', 'TRAFEGO_PAGO'));

        return view('reports.show', [
            'batch' => $batch,
            'batches' => $batches,
            'allBatches' => $this->scopedBatches()->orderByDesc('year')->orderByDesc('month')->get(),
            'metrics' => $metrics,
        ]);
    }

    public function pdf(
        UploadBatch $batch,
        Request $request,
        MetricsCalculatorService $metricsService,
        PdfReportGeneratorService $pdfService
    ) {
        $this->ensureBatchAccess($batch);
        $batches = $this->resolveBatches($batch, $request);
        $metrics = $metricsService->calculateForBatches($batches, $request->query('origem', 'TRAFEGO_PAGO'));

        $months = $metrics['months'];
        $suffix = count($months) > 1 ? implode('-', array_map(fn ($m) => substr($m, 5, 2), $months)) : $batch->month;
        $filename = sprintf('Relatorio_ComunicaSaaS_%04d_%s.pdf', $batch->year, $suffix);

        return $pdfService->generate($batch, $metrics, $filename);
    }

    public function remarketingCsv(
        UploadBatch $batch,
        Request $request,
        RemarketingExporterService $exporter
    ) {
        $this->ensureBatchAccess($batch);
        return $exporter->export($batch, [
            'origem' => $request->query('origem'),
            'mes' => $request->query('mes'),
        ]);
    }

    private function resolveBatches(UploadBatch $batch, Request $request)
    {
        $months = $request->query('meses');
        if (!$months) {
            return collect([$batch]);
        }

        $monthList = collect(explode(',', $months))
            ->map(fn ($m) => (int) trim($m))
            ->filter(fn ($m) => $m >= 1 && $m <= 12)
            ->unique()
            ->values();

        if ($monthList->isEmpty()) {
            return collect([$batch]);
        }

        return $this->scopedBatches()->where('year', $batch->year)
            ->whereIn('month', $monthList)
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }
}

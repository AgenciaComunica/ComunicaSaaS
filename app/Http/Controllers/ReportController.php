<?php

namespace App\Http\Controllers;

use App\Models\UploadBatch;
use App\Services\MetricsCalculatorService;
use App\Services\PdfReportGeneratorService;
use App\Services\RemarketingExporterService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', [
            'batches' => UploadBatch::orderByDesc('year')->orderByDesc('month')->get(),
        ]);
    }

    public function show(UploadBatch $batch, Request $request, MetricsCalculatorService $metricsService): View
    {
        $batches = $this->resolveBatches($batch, $request);
        $metrics = $metricsService->calculateForBatches($batches, $request->query('origem', 'TRAFEGO_PAGO'));

        return view('reports.show', [
            'batch' => $batch,
            'batches' => $batches,
            'metrics' => $metrics,
        ]);
    }

    public function pdf(
        UploadBatch $batch,
        Request $request,
        MetricsCalculatorService $metricsService,
        PdfReportGeneratorService $pdfService
    ) {
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

        return UploadBatch::where('year', $batch->year)
            ->whereIn('month', $monthList)
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }
}

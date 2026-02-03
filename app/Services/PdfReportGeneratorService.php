<?php

namespace App\Services;

use App\Models\UploadBatch;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfReportGeneratorService
{
    public function generate(UploadBatch $batch, array $metrics, string $filename)
    {
        $charts = $this->buildCharts($metrics);

        $pdf = Pdf::loadView('reports.pdf', [
            'batch' => $batch,
            'metrics' => $metrics,
            'charts' => $charts,
        ])->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true);

        return $pdf->download($filename);
    }

    private function buildCharts(array $metrics): array
    {
        $months = $metrics['months'];

        $investimentos = [];
        $receitas = [];
        foreach ($months as $month) {
            $investimentos[] = $metrics['meta']['by_month'][$month]['spend'] ?? 0;
            $receitas[] = $metrics['funnel_selected']['by_month'][$month]['receita'] ?? 0;
        }

        $temperaturas = [
            'FRIO' => $metrics['funnel_selected']['total']['temperaturas']['FRIO'] ?? 0,
            'QUENTE' => $metrics['funnel_selected']['total']['temperaturas']['QUENTE'] ?? 0,
            'MUITO_QUENTE' => $metrics['funnel_selected']['total']['temperaturas']['MUITO_QUENTE'] ?? 0,
            'SEM_TEMPERATURA' => $metrics['funnel_selected']['total']['temperaturas']['SEM_TEMPERATURA'] ?? 0,
        ];

        $custos = [
            'FRIO' => [],
            'QUENTE' => [],
            'MUITO_QUENTE' => [],
            'SEM_TEMPERATURA' => [],
        ];
        foreach ($months as $month) {
            foreach (array_keys($custos) as $temp) {
                $custos[$temp][] = $metrics['custo_temperatura']['by_month'][$month][$temp] ?? 0;
            }
        }

        return [
            'invest_vs_receita' => $this->quickChartUrl([
                'type' => 'bar',
                'data' => [
                    'labels' => $months,
                    'datasets' => [
                        [
                            'label' => 'Investimento',
                            'backgroundColor' => '#0d6efd',
                            'data' => $investimentos,
                        ],
                        [
                            'label' => 'Receita',
                            'backgroundColor' => '#198754',
                            'data' => $receitas,
                        ],
                    ],
                ],
            ]),
            'temperatura_distribuicao' => $this->quickChartUrl([
                'type' => 'doughnut',
                'data' => [
                    'labels' => ['Frio', 'Quente', 'Muito Quente', 'Sem Temperatura'],
                    'datasets' => [
                        [
                            'backgroundColor' => ['#6c757d', '#fd7e14', '#dc3545', '#0dcaf0'],
                            'data' => array_values($temperaturas),
                        ],
                    ],
                ],
            ]),
            'custo_temperatura' => $this->quickChartUrl([
                'type' => 'bar',
                'data' => [
                    'labels' => $months,
                    'datasets' => [
                        [
                            'label' => 'Frio',
                            'backgroundColor' => '#6c757d',
                            'data' => $custos['FRIO'],
                        ],
                        [
                            'label' => 'Quente',
                            'backgroundColor' => '#fd7e14',
                            'data' => $custos['QUENTE'],
                        ],
                        [
                            'label' => 'Muito Quente',
                            'backgroundColor' => '#dc3545',
                            'data' => $custos['MUITO_QUENTE'],
                        ],
                        [
                            'label' => 'Sem Temperatura',
                            'backgroundColor' => '#0dcaf0',
                            'data' => $custos['SEM_TEMPERATURA'],
                        ],
                    ],
                ],
            ]),
        ];
    }

    private function quickChartUrl(array $chart): string
    {
        $json = json_encode($chart, JSON_UNESCAPED_SLASHES);
        $encoded = urlencode($json);

        return 'https://quickchart.io/chart?c='.$encoded.'&width=800&height=400';
    }
}

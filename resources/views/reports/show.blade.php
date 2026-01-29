<x-app-layout>
    @php
        $metaTotal = $metrics['meta']['total'];
        $funnelSelected = $metrics['funnel_selected']['total'];
        $roiTotal = $metrics['roi']['total'];
        $months = $metrics['months'];
        $monthsArray = $months->values()->all();
        $originFilter = $metrics['origin_filter'];
        $originLabel = $originFilter === 'TRAFEGO_PAGO' ? 'Ads' : ($originFilter === 'ORGANICO' ? 'Orgânico' : 'Completo');
        $roiSummary = $metrics['roi_summary'];
    @endphp

    @php
        $query = request()->query();
        $missingMeta = $batch->parse_stats['meta']['missing'] ?? [];
        $missingIntel = $batch->parse_stats['intelbras']['missing'] ?? [];
        $metaLabels = [
            'spend' => 'Investimento',
            'impressions' => 'Impressões',
            'clicks' => 'Cliques',
            'ctr' => 'CTR',
            'cpc' => 'CPC',
            'leads' => 'Leads',
            'results' => 'Resultados',
        ];
        $intelLabels = [
            'first_message' => '1ª Mensagem (Origem)',
            'temperature' => 'Temperatura/Tag',
            'valor_venda' => 'Valor Venda',
            'name' => 'Nome',
            'phone' => 'Telefone/WhatsApp',
            'email' => 'Email',
        ];
        $missingMetaText = collect($missingMeta)->map(fn($k) => $metaLabels[$k] ?? $k)->values();
        $missingIntelText = collect($missingIntel)->map(fn($k) => $intelLabels[$k] ?? $k)->values();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 mb-1">Relatório {{ sprintf('%02d-%04d', $batch->month, $batch->year) }}</h1>
            <div class="text-muted">Visão web e exportável em PDF</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.pdf', $batch, $query) }}" class="btn btn-primary">Baixar PDF</a>
            <a href="{{ route('reports.remarketing', $batch, $query) }}" class="btn btn-outline-secondary">Baixar Remarketing (CSV)</a>
        </div>
    </div>


    <div class="card p-3 mb-4">
        <form class="row g-2 align-items-end" method="GET" action="{{ route('reports.show', $batch) }}" id="reportFilterForm">
            <div class="col-md-4">
                <label class="form-label">Relatorio</label>
                <select class="form-select" id="reportSelect">
                    @foreach($allBatches as $item)
                        <option value="{{ route('reports.show', $item) }}" @selected($item->id === $batch->id)>
                            {{ sprintf('%02d-%04d', $item->month, $item->year) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Filtro de resultados</label>
                <select class="form-select" name="origem">
                    <option value="TRAFEGO_PAGO" @selected($originFilter === 'TRAFEGO_PAGO')>Ads</option>
                    <option value="ORGANICO" @selected($originFilter === 'ORGANICO')>Orgânico</option>
                    <option value="ALL" @selected($originFilter === 'ALL')>Completo</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary" type="submit">Aplicar</button>
            </div>

            @if($missingMetaText->isNotEmpty() || $missingIntelText->isNotEmpty())
                <div class="small text-muted mt-2">
                    <strong>Campos não identificados nas planilhas:</strong>
                    @if($missingMetaText->isNotEmpty())
                        <div>Meta Ads: {{ $missingMetaText->implode(', ') }}</div>
                    @endif
                    @if($missingIntelText->isNotEmpty())
                        <div>CRM/Vendas: {{ $missingIntelText->implode(', ') }}</div>
                    @endif
                    <div>Os KPIs ausentes aparecem como N/A ou 0 no relatório.</div>
                </div>
            @endif
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('reportSelect');
            if (!select) return;
            select.addEventListener('change', function () {
                const url = new URL(select.value, window.location.origin);
                const params = new URLSearchParams(window.location.search);
                const origem = params.get('origem');
                if (origem) {
                    url.searchParams.set('origem', origem);
                }
                window.location.href = url.toString();
            });
        });
    </script>
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h6 mb-0">Resumo executivo</h2>
            <span class="badge bg-light text-dark">ROI/ROAS ({{ $originLabel }})</span>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 border rounded bg-white">
                    <div class="text-muted small">ROI do período</div>
                    <div class="h4 mb-1">
                        {{ is_null($roiSummary['roi']) ? 'N/A' : number_format($roiSummary['roi'] * 100, 1, ',', '.') . '%' }}
                    </div>
                    <div class="small text-muted">
                        @if($roiSummary['previous_month'])
                            vs {{ $roiSummary['previous_month'] }}:
                            @if(!is_null($roiSummary['roi_delta']))
                                @php
                                    $roiDeltaClass = $roiSummary['roi_delta'] > 0 ? 'text-success' : ($roiSummary['roi_delta'] < 0 ? 'text-danger' : 'text-warning');
                                    $roiDeltaIcon = $roiSummary['roi_delta'] > 0 ? '↑' : ($roiSummary['roi_delta'] < 0 ? '↓' : '→');
                                @endphp
                                <span class="{{ $roiDeltaClass }}">
                                    {{ $roiDeltaIcon }}
                                    {{ number_format($roiSummary['roi_delta'] * 100, 1, ',', '.') }}%
                                </span>
                            @else
                                N/A
                            @endif
                        @else
                            sem mês anterior
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 border rounded bg-white">
                    <div class="text-muted small">ROAS do período</div>
                    <div class="h4 mb-1">
                        {{ is_null($roiSummary['roas']) ? 'N/A' : number_format($roiSummary['roas'], 2, ',', '.') }}
                    </div>
                    <div class="small text-muted">
                        @if($roiSummary['previous_month'])
                            vs {{ $roiSummary['previous_month'] }}:
                            @if(!is_null($roiSummary['roas_delta']))
                                @php
                                    $roasDeltaClass = $roiSummary['roas_delta'] > 0 ? 'text-success' : ($roiSummary['roas_delta'] < 0 ? 'text-danger' : 'text-warning');
                                    $roasDeltaIcon = $roiSummary['roas_delta'] > 0 ? '↑' : ($roiSummary['roas_delta'] < 0 ? '↓' : '→');
                                @endphp
                                <span class="{{ $roasDeltaClass }}">
                                    {{ $roasDeltaIcon }}
                                    {{ number_format($roiSummary['roas_delta'] * 100, 1, ',', '.') }}%
                                </span>
                            @else
                                N/A
                            @endif
                        @else
                            sem mês anterior
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="text-muted small mt-2">ROI = (Receita - Investimento) / Investimento. ROAS = Receita / Investimento.</div>
    </div>

    <div class="row g-3 mb-4">
        @if($originFilter !== 'ORGANICO')
            <div class="col-12 col-md-3">
                <div class="card p-3">
                    <div class="text-muted">Investimento (Ads)</div>
                    <div class="h5 mb-0">R$ {{ number_format($metaTotal['spend'], 2, ',', '.') }}</div>
                </div>
            </div>
        @endif
        <div class="col-12 col-md-3">
            <div class="card p-3">
                <div class="text-muted">Leads ({{ $originLabel }})</div>
                <div class="h5 mb-0">{{ $funnelSelected['leads'] }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3">
                <div class="text-muted">Receita ({{ $originLabel }})</div>
                <div class="h5 mb-0">R$ {{ number_format($funnelSelected['receita'], 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card p-3">
                <h3 class="h6">
                    @if($originFilter === 'ORGANICO')
                        Receita (Orgânico)
                    @else
                        Investimento vs Receita ({{ $originLabel }})
                    @endif
                </h3>
                <div class="chart-box-lg">
                    <canvas id="chartInvest"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card p-3">
                <h3 class="h6">Distribuição de Temperatura ({{ $originLabel }})</h3>
                <div class="chart-box-lg">
                    <canvas id="chartTemp"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card p-3">
                <h3 class="h6">Custo por Lead ({{ $originLabel }})</h3>
                <div class="chart-box-sm">
                    <canvas id="chartCost"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-4 mb-4">
        <h2 class="h6">Marketing (Meta Ads)</h2>
        @if($originFilter === 'ORGANICO')
            <p class="text-muted">Sem dados de Meta Ads para o filtro Orgânico.</p>
        @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mês</th>
                        <th>Investimento</th>
                        <th>Impressões</th>
                        <th>Cliques</th>
                        <th>Resultados</th>
                        <th>CTR</th>
                        <th>CPC</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($metrics['meta']['by_month'] as $month => $row)
                        <tr>
                            <td>{{ $month }}</td>
                            <td>R$ {{ number_format($row['spend'], 2, ',', '.') }}</td>
                            <td>{{ number_format($row['impressions']) }}</td>
                            <td>{{ number_format($row['clicks']) }}</td>
                            <td>{{ number_format($row['results'] ?? 0) }}</td>
                            <td>{{ number_format($row['ctr'] * 100, 2, ',', '.') }}%</td>
                            <td>R$ {{ number_format($row['cpc'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="table-light">
                        <td>Total</td>
                        <td>R$ {{ number_format($metaTotal['spend'], 2, ',', '.') }}</td>
                        <td>{{ number_format($metaTotal['impressions']) }}</td>
                        <td>{{ number_format($metaTotal['clicks']) }}</td>
                        <td>{{ number_format($metaTotal['results'] ?? 0) }}</td>
                        <td>{{ number_format($metaTotal['ctr'] * 100, 2, ',', '.') }}%</td>
                        <td>R$ {{ number_format($metaTotal['cpc'], 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="card p-4 mb-4">
        <h2 class="h6">Comercial (Funil CRM Vendas - {{ $originLabel }})</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mês</th>
                        <th>Leads</th>
                        <th>Frio</th>
                        <th>Quente</th>
                        <th>Muito Quente</th>
                        <th>Sem Temp.</th>
                        <th>Vendas</th>
                        <th>Receita</th>
                        <th>Conv.</th>
                        <th>Ticket</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($metrics['funnel_selected']['by_month'] as $month => $row)
                        <tr>
                            <td>{{ $month }}</td>
                            <td>{{ $row['leads'] }}</td>
                            <td>{{ $row['temperaturas']['FRIO'] }} ({{ $row['percentuais']['FRIO'] ? number_format($row['percentuais']['FRIO'] * 100, 1, ',', '.') : '0' }}%)</td>
                            <td>{{ $row['temperaturas']['QUENTE'] }} ({{ $row['percentuais']['QUENTE'] ? number_format($row['percentuais']['QUENTE'] * 100, 1, ',', '.') : '0' }}%)</td>
                            <td>{{ $row['temperaturas']['MUITO_QUENTE'] }} ({{ $row['percentuais']['MUITO_QUENTE'] ? number_format($row['percentuais']['MUITO_QUENTE'] * 100, 1, ',', '.') : '0' }}%)</td>
                            <td>{{ $row['temperaturas']['SEM_TEMPERATURA'] }} ({{ $row['percentuais']['SEM_TEMPERATURA'] ? number_format($row['percentuais']['SEM_TEMPERATURA'] * 100, 1, ',', '.') : '0' }}%)</td>
                            <td>{{ $row['vendas'] }}</td>
                            <td>R$ {{ number_format($row['receita'], 2, ',', '.') }}</td>
                            <td>{{ $row['taxa_conversao'] ? number_format($row['taxa_conversao'] * 100, 2, ',', '.') : 'N/A' }}%</td>
                            <td>{{ $row['ticket_medio'] ? 'R$ '.number_format($row['ticket_medio'], 2, ',', '.') : 'N/A' }}</td>
                        </tr>
                    @endforeach
                    <tr class="table-light">
                        <td>Total</td>
                        <td>{{ $funnelSelected['leads'] }}</td>
                        <td>{{ $funnelSelected['temperaturas']['FRIO'] }} ({{ $funnelSelected['percentuais']['FRIO'] ? number_format($funnelSelected['percentuais']['FRIO'] * 100, 1, ',', '.') : '0' }}%)</td>
                        <td>{{ $funnelSelected['temperaturas']['QUENTE'] }} ({{ $funnelSelected['percentuais']['QUENTE'] ? number_format($funnelSelected['percentuais']['QUENTE'] * 100, 1, ',', '.') : '0' }}%)</td>
                        <td>{{ $funnelSelected['temperaturas']['MUITO_QUENTE'] }} ({{ $funnelSelected['percentuais']['MUITO_QUENTE'] ? number_format($funnelSelected['percentuais']['MUITO_QUENTE'] * 100, 1, ',', '.') : '0' }}%)</td>
                        <td>{{ $funnelSelected['temperaturas']['SEM_TEMPERATURA'] }} ({{ $funnelSelected['percentuais']['SEM_TEMPERATURA'] ? number_format($funnelSelected['percentuais']['SEM_TEMPERATURA'] * 100, 1, ',', '.') : '0' }}%)</td>
                        <td>{{ $funnelSelected['vendas'] }}</td>
                        <td>R$ {{ number_format($funnelSelected['receita'], 2, ',', '.') }}</td>
                        <td>{{ $funnelSelected['taxa_conversao'] ? number_format($funnelSelected['taxa_conversao'] * 100, 2, ',', '.') : 'N/A' }}%</td>
                        <td>{{ $funnelSelected['ticket_medio'] ? 'R$ '.number_format($funnelSelected['ticket_medio'], 2, ',', '.') : 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-4 mb-4">
        <h2 class="h6">ROI / ROAS ({{ $originLabel }})</h2>
        <p class="text-muted mb-2">ROI = (Receita - Investimento) / Investimento. ROAS = Receita / Investimento.</p>
        @if($originFilter === 'ORGANICO')
            <p class="text-muted">ROI/ROAS não aplicável para Orgânico (investimento zero).</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mês</th>
                            <th>ROI</th>
                            <th>ROAS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($metrics['roi']['by_month'] as $month => $row)
                            <tr>
                                <td>{{ $month }}</td>
                                <td>{{ is_null($row['roi']) ? 'N/A' : number_format($row['roi'] * 100, 2, ',', '.') . '%' }}</td>
                                <td>{{ is_null($row['roas']) ? 'N/A' : number_format($row['roas'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="table-light">
                            <td>Total</td>
                            <td>{{ is_null($roiTotal['roi']) ? 'N/A' : number_format($roiTotal['roi'] * 100, 2, ',', '.') . '%' }}</td>
                            <td>{{ is_null($roiTotal['roas']) ? 'N/A' : number_format($roiTotal['roas'], 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card p-4 mb-4">
        <h2 class="h6">Remarketing</h2>
        <p class="text-muted">Exportação de leads Quentes e Muito Quentes.</p>
        <form class="row g-2" action="{{ route('reports.remarketing', $batch) }}" method="GET">
            <div class="col-md-3">
                <select class="form-select" name="origem">
                    <option value="">Todas as origens</option>
                    <option value="TRAFEGO_PAGO">Tráfego Pago</option>
                    <option value="ORGANICO">Orgânico</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="mes">
                    <option value="">Todos os meses</option>
                    @foreach($months as $month)
                        <option value="{{ (int) substr($month, 5, 2) }}">{{ $month }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary" type="submit">Exportar</button>
            </div>
        </form>
    </div>

    <style>
        .chart-box-lg { height: 400px; position: relative; }
        .chart-box-lg canvas { width: 100% !important; height: 100% !important; }
        .chart-box-sm { height: 220px; position: relative; }
        .chart-box-sm canvas { width: 100% !important; height: 100% !important; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    <script>
        Chart.register(ChartDataLabels);
        const months = @json($monthsArray);
        const investData = @json(array_map(fn($m) => $metrics['meta']['by_month'][$m]['spend'] ?? 0, $monthsArray));
        const receitaData = @json(array_map(fn($m) => $metrics['funnel_selected']['by_month'][$m]['receita'] ?? 0, $monthsArray));
        const tempData = {
            frio: {{ $funnelSelected['temperaturas']['FRIO'] ?? 0 }},
            quente: {{ $funnelSelected['temperaturas']['QUENTE'] ?? 0 }},
            muito: {{ $funnelSelected['temperaturas']['MUITO_QUENTE'] ?? 0 }},
            sem: {{ $funnelSelected['temperaturas']['SEM_TEMPERATURA'] ?? 0 }},
        };
        const costData = {
            frio: @json(array_map(fn($m) => $metrics['custo_temperatura']['by_month'][$m]['FRIO'] ?? 0, $monthsArray)),
            quente: @json(array_map(fn($m) => $metrics['custo_temperatura']['by_month'][$m]['QUENTE'] ?? 0, $monthsArray)),
            muito: @json(array_map(fn($m) => $metrics['custo_temperatura']['by_month'][$m]['MUITO_QUENTE'] ?? 0, $monthsArray)),
            sem: @json(array_map(fn($m) => $metrics['custo_temperatura']['by_month'][$m]['SEM_TEMPERATURA'] ?? 0, $monthsArray)),
        };

        const numberPt = new Intl.NumberFormat('pt-BR');
        const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

        const investDatasets = [
            @if($originFilter !== 'ORGANICO')
                { label: 'Investimento', data: investData, backgroundColor: '#0d6efd' },
            @endif
            { label: 'Receita', data: receitaData, backgroundColor: '#198754' },
        ];

        new Chart(document.getElementById('chartInvest'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: investDatasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#1b1f2a',
                        formatter: (value) => currency.format(value),
                        font: { size: 10, weight: '600' },
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${currency.format(ctx.parsed.y)}`,
                        },
                    },
                },
            },
        });

        new Chart(document.getElementById('chartTemp'), {
            type: 'doughnut',
            data: {
                labels: ['Frio', 'Quente', 'Muito Quente', 'Sem Temperatura'],
                datasets: [
                    {
                        data: [tempData.frio, tempData.quente, tempData.muito, tempData.sem],
                        backgroundColor: ['#0d6efd', '#fd7e14', '#dc3545', '#6c757d'],
                    }
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: @json($originFilter === 'ORGANICO' ? 'right' : 'bottom'),
                        align: @json($originFilter === 'ORGANICO' ? 'center' : 'center'),
                    },
                    datalabels: {
                        color: '#1b1f2a',
                        anchor: 'end',
                        align: 'end',
                        offset: 8,
                        formatter: (value, ctx) => {
                            const data = ctx.chart.data.datasets[0].data;
                            const total = data.reduce((a, b) => a + b, 0);
                            if (!total || value === 0) return '';
                            const percent = (value / total) * 100;
                            return `${numberPt.format(percent.toFixed(1))}%`;
                        },
                        font: { size: 10, weight: '600' },
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.label}: ${numberPt.format(ctx.parsed)}`,
                        },
                    },
                },
            }
        });

        new Chart(document.getElementById('chartCost'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    { label: 'Frio', data: costData.frio, backgroundColor: '#0d6efd' },
                    { label: 'Quente', data: costData.quente, backgroundColor: '#fd7e14' },
                    { label: 'Muito Quente', data: costData.muito, backgroundColor: '#dc3545' },
                    { label: 'Sem Temperatura', data: costData.sem, backgroundColor: '#6c757d' },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => currency.format(value),
                        },
                    },
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        align: 'start',
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#1b1f2a',
                        formatter: (value) => currency.format(value),
                        font: { size: 10, weight: '600' },
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${currency.format(ctx.parsed.y)}`,
                        },
                    },
                },
            },
        });
    </script>
</x-app-layout>

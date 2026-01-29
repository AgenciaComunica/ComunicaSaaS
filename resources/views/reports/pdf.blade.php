<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1b1f2a; }
        h1, h2, h3 { margin: 0 0 6px; }
        .cover { text-align: center; padding: 80px 20px; border-bottom: 2px solid #e2e6ee; margin-bottom: 20px; }
        .section { margin-bottom: 20px; }
        .card { border: 1px solid #e2e6ee; padding: 12px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e2e6ee; padding: 6px; text-align: left; }
        th { background: #f0f2f6; }
        .grid { display: table; width: 100%; }
        .grid .col { display: table-cell; width: 25%; padding: 6px; vertical-align: top; }
        .muted { color: #666; font-size: 11px; }
        .img { width: 100%; margin: 8px 0; }
        .footer { margin-top: 30px; font-size: 10px; color: #666; text-align: center; }
    </style>
</head>
<body>
    @php
        $metaTotal = $metrics['meta']['total'];
        $funnelSelected = $metrics['funnel_selected']['total'];
        $roiTotal = $metrics['roi']['total'];
        $originFilter = $metrics['origin_filter'];
        $originLabel = $originFilter === 'TRAFEGO_PAGO' ? 'Ads' : ($originFilter === 'ORGANICO' ? 'Orgânico' : 'Completo');
        $roiSummary = $metrics['roi_summary'];
    @endphp

    <div class="cover">

    @php
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

    @if($missingMetaText->isNotEmpty() || $missingIntelText->isNotEmpty())
        <div class="section">
            <h2>Campos não identificados</h2>
            @if($missingMetaText->isNotEmpty())
                <div class="muted">Meta Ads: {{ $missingMetaText->implode(', ') }}</div>
            @endif
            @if($missingIntelText->isNotEmpty())
                <div class="muted">CRM/Vendas: {{ $missingIntelText->implode(', ') }}</div>
            @endif
            <div class="muted">Os KPIs ausentes aparecem como N/A ou 0 no relatório.</div>
        </div>
    @endif

        <h1>Relatório de Tráfego Pago – Comunica SaaS</h1>
        <div class="muted">Período: {{ $batch->display_label }}</div>
    </div>

    <div class="section">
        <h2>Resumo executivo</h2>
        <div class="grid">
            <div class="col card">
                <strong>ROI ({{ $originLabel }})</strong>
                <div>{{ is_null($roiSummary['roi']) ? 'N/A' : number_format($roiSummary['roi'] * 100, 1, ',', '.') . '%' }}</div>
                <div class="muted">
                    @if($roiSummary['previous_month'])
                        vs {{ $roiSummary['previous_month'] }}:
                        {{ is_null($roiSummary['roi_delta']) ? 'N/A' : number_format($roiSummary['roi_delta'] * 100, 1, ',', '.') . '%' }}
                    @else
                        sem mês anterior
                    @endif
                </div>
            </div>
            <div class="col card">
                <strong>ROAS ({{ $originLabel }})</strong>
                <div>{{ is_null($roiSummary['roas']) ? 'N/A' : number_format($roiSummary['roas'], 2, ',', '.') }}</div>
                <div class="muted">
                    @if($roiSummary['previous_month'])
                        vs {{ $roiSummary['previous_month'] }}:
                        {{ is_null($roiSummary['roas_delta']) ? 'N/A' : number_format($roiSummary['roas_delta'] * 100, 1, ',', '.') . '%' }}
                    @else
                        sem mês anterior
                    @endif
                </div>
            </div>
        </div>
        <div class="muted">ROI = (Receita - Investimento) / Investimento. ROAS = Receita / Investimento.</div>
    </div>

    <div class="section">
        <h2>Visão Geral</h2>
        <div class="grid">
            <div class="col card">
                <strong>Investimento</strong>
                <div>R$ {{ number_format($metaTotal['spend'], 2, ',', '.') }}</div>
            </div>
            <div class="col card">
                <strong>Leads ({{ $originLabel }})</strong>
                <div>{{ $funnelSelected['leads'] }}</div>
            </div>
            <div class="col card">
                <strong>Receita ({{ $originLabel }})</strong>
                <div>R$ {{ number_format($funnelSelected['receita'], 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Marketing (Meta Ads)</h2>
        <div class="muted">Investimento vs Receita ({{ $originLabel }})</div>
        <img class="img" src="{{ $charts['invest_vs_receita'] }}" alt="Investimento vs Receita (Ads)">
        <table>
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
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Comercial (Funil CRM Vendas - {{ $originLabel }})</h2>
        <div class="muted">Distribuição de Temperatura ({{ $originLabel }})</div>
        <img class="img" src="{{ $charts['temperatura_distribuicao'] }}" alt="Distribuição de temperatura (Ads)">
        <table>
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
                </tr>
            </thead>
            <tbody>
                @foreach($metrics['funnel_selected']['by_month'] as $month => $row)
                    <tr>
                        <td>{{ $month }}</td>
                        <td>{{ $row['leads'] }}</td>
                        <td>{{ $row['temperaturas']['FRIO'] }}</td>
                        <td>{{ $row['temperaturas']['QUENTE'] }}</td>
                        <td>{{ $row['temperaturas']['MUITO_QUENTE'] }}</td>
                        <td>{{ $row['temperaturas']['SEM_TEMPERATURA'] }}</td>
                        <td>{{ $row['vendas'] }}</td>
                        <td>R$ {{ number_format($row['receita'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Custo por Temperatura (Tráfego Pago)</h2>
        <img class="img" src="{{ $charts['custo_temperatura'] }}" alt="Custo por temperatura">
    </div>

    <div class="section">
        <h2>ROI / ROAS ({{ $originLabel }})</h2>
        <div class="muted">ROI = (Receita - Investimento) / Investimento. ROAS = Receita / Investimento.</div>
        @if($originFilter === 'ORGANICO')
            <div class="muted">ROI/ROAS não aplicável para Orgânico (investimento zero).</div>
        @else
            <table>
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
                    <tr>
                        <td>Total</td>
                        <td>{{ is_null($roiTotal['roi']) ? 'N/A' : number_format($roiTotal['roi'] * 100, 2, ',', '.') . '%' }}</td>
                        <td>{{ is_null($roiTotal['roas']) ? 'N/A' : number_format($roiTotal['roas'], 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    </div>

    <div class="section">
        <h2>Remarketing</h2>
        <p class="muted">Exportação disponível no painel web para leads Quentes e Muito Quentes.</p>
    </div>

    <div class="footer"></div>
</body>
</html>

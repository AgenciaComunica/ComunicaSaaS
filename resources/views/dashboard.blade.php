<x-app-layout>
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Dashboard</h1>
            <div class="text-muted">Visão geral do marketing digital da sua empresa</div>
            <span class="badge bg-light text-muted border mt-2">Dados ilustrativos (placeholder)</span>
        </div>
        <form method="GET" class="d-flex gap-2 align-items-end">
            <div>
                <label class="form-label mb-1 small">Mês</label>
                <select name="month" class="form-select form-select-sm">
                    @foreach($months as $m)
                        <option value="{{ $m['value'] }}" @selected($m['value'] == $month)>{{ ucfirst($m['label']) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label mb-1 small">Ano</label>
                <select name="year" class="form-select form-select-sm">
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-sm btn-primary" type="submit">Filtrar</button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-3">
            <div class="card p-3 border-0 shadow-sm h-100">
                <div class="text-muted">Investimento em Ads</div>
                <div class="h5 mb-0">R$ {{ number_format($kpis['ad_spend'], 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-12 col-lg-3">
            <div class="card p-3 border-0 shadow-sm h-100">
                <div class="text-muted">Receita</div>
                <div class="h5 mb-0">R$ {{ number_format($kpis['revenue'], 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-12 col-lg-3">
            <div class="card p-3 border-0 shadow-sm h-100">
                <div class="text-muted">ROI / ROAS</div>
                <div class="h5 mb-0">ROI {{ number_format($kpis['roi'] * 100, 1, ',', '.') }}% | ROAS {{ number_format($kpis['roas'], 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-12 col-lg-3">
            <div class="card p-3 border-0 shadow-sm h-100">
                <div class="text-muted">Vendas</div>
                <div class="h5 mb-0">{{ number_format($kpis['sales']) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h6 text-uppercase text-muted mb-0">Redes sociais (Instagram + Facebook)</h2>
                <span class="small text-muted">Conecte sua conta Meta para atualizar automaticamente</span>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Alcance</div>
                <div class="h5 mb-0">{{ number_format($kpis['social_reach']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Impressões</div>
                <div class="h5 mb-0">{{ number_format($kpis['social_impressions']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Visualizações de perfil</div>
                <div class="h5 mb-0">{{ number_format($kpis['social_profile_views']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Cliques em links</div>
                <div class="h5 mb-0">{{ number_format($kpis['social_clicks']) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h6 text-uppercase text-muted mb-0">Website</h2>
                <span class="small text-muted">Integração futura com Google Analytics</span>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Sessões</div>
                <div class="h5 mb-0">{{ number_format($kpis['site_sessions']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Usuários</div>
                <div class="h5 mb-0">{{ number_format($kpis['site_users']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Pageviews</div>
                <div class="h5 mb-0">{{ number_format($kpis['site_pageviews']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Taxa de rejeição</div>
                <div class="h5 mb-0">{{ number_format($kpis['site_bounce_rate'] * 100, 1, ',', '.') }}%</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <h2 class="h6 text-uppercase text-muted mb-0">Tráfego pago e qualidade dos leads</h2>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Conversas iniciadas (Ads)</div>
                <div class="h5 mb-0">{{ number_format($kpis['paid_conversations']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Leads frios</div>
                <div class="h5 mb-0">{{ number_format($kpis['leads_frio']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Leads quentes</div>
                <div class="h5 mb-0">{{ number_format($kpis['leads_quente']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Leads muito quentes</div>
                <div class="h5 mb-0">{{ number_format($kpis['leads_muito_quente']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Sem temperatura</div>
                <div class="h5 mb-0">{{ number_format($kpis['leads_sem_temp']) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <h2 class="h6 text-uppercase text-muted mb-0">Resultados comerciais</h2>
        </div>
        <div class="col-12 col-md-4">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Taxa de conversão</div>
                <div class="h5 mb-0">{{ number_format($kpis['conversion_rate'] * 100, 2, ',', '.') }}%</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Ticket médio</div>
                <div class="h5 mb-0">R$ {{ number_format($kpis['ticket'], 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card p-3 border-0 shadow-sm">
                <div class="text-muted">Receita total</div>
                <div class="h5 mb-0">R$ {{ number_format($kpis['revenue'], 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
</x-app-layout>

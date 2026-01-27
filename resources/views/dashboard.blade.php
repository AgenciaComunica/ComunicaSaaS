<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Dashboard</h1>
            <div class="text-muted">Visão geral do painel de tráfego pago</div>
        </div>
        <a href="{{ route('uploads.create') }}" class="btn btn-primary">Gerar relatório</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card p-3">
                <div class="text-muted">Batches processados</div>
                <div class="h4 mb-0">{{ $batchCount }}</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card p-3">
                <div class="text-muted">Leads normalizados</div>
                <div class="h4 mb-0">{{ $leadCount }}</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card p-3">
                <div class="text-muted">Último batch</div>
                <div class="h4 mb-0">{{ $latestBatch?->display_label ?? 'Nenhum' }}</div>
            </div>
        </div>
    </div>

    <div class="card p-4">
        <h2 class="h6">Ações rápidas</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('uploads.index') }}" class="btn btn-outline-secondary">Ver uploads</a>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Ver relatórios</a>
            @if(auth()->user()?->isAdmin())
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Gerenciar usuários</a>
            @endif
        </div>
    </div>
</x-app-layout>

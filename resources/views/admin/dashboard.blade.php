<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Admin</h1>
            <div class="text-muted">Visão geral da plataforma</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card p-3">
                <div class="text-muted">Empresas (usuários)</div>
                <div class="h4 mb-0">{{ $userCount }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card p-3">
                <div class="text-muted">Relatórios</div>
                <div class="h4 mb-0">{{ $batchCount }}</div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Dashboard</h1>
            <div class="text-muted">Nenhum relatório gerado ainda</div>
        </div>
    </div>

    <div class="card p-4">
        <p class="mb-3">Assim que um upload for processado, o relatório mais recente aparecerá aqui automaticamente.</p>
        <a href="{{ route('uploads.create') }}" class="btn btn-primary">Gerar primeiro relatório</a>
    </div>
</x-app-layout>

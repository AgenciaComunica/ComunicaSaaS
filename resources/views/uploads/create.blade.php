<x-app-layout>
    <div class="mb-4">
        <h1 class="h4 mb-1">Novo relatório</h1>
        <div class="text-muted">Envie Meta Ads (CSV) e Intelbras (XLSX)</div>
    </div>

    <div class="card p-4">
        <form method="POST" action="{{ route('uploads.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Período</label>
                    <input class="form-control form-control-sm" name="period" type="month" value="{{ old('period', now()->format('Y-m')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Meta Ads (CSV)</label>
                    <input class="form-control form-control-sm" type="file" name="meta_csv" accept=".csv" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Intelbras (XLSX)</label>
                    <input class="form-control form-control-sm" type="file" name="intelbras_xlsx" accept=".xlsx" required>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Processar</button>
                <a href="{{ route('uploads.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>

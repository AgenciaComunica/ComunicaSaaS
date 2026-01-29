<x-app-layout>
    <div class="mb-4">
        <h1 class="h4 mb-1">Novo relatório</h1>
        <div class="text-muted">Envie Meta Ads (CSV) e CRM Vendas (XLSX)</div>
    </div>

    <div class="card p-4">
        <form id="uploadForm" method="POST" action="{{ route('uploads.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="force_replace" id="force_replace" value="0">

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
                    <label class="form-label">CRM Vendas (XLSX)</label>
                    <input class="form-control form-control-sm" type="file" name="intelbras_xlsx" accept=".xlsx" required>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Processar</button>
                <a href="{{ route('uploads.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>


    @if(session('confirm_replace'))
        @php
            $confirm = session('confirm_replace');
        @endphp
        <div class="modal fade" id="modalReplace" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Substituir relatório?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Já existe um relatório para <strong>{{ $confirm['label'] }}</strong>.
                        Deseja substituir pelo novo upload?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="confirmReplaceBtn">Substituir</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalEl = document.getElementById('modalReplace');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                document.getElementById('confirmReplaceBtn').addEventListener('click', function () {
                    document.getElementById('force_replace').value = '1';
                    modal.hide();
                    document.getElementById('uploadForm').submit();
                });
            });
        </script>
    @endif

</x-app-layout>

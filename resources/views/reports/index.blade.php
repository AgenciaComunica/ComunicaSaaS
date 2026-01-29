<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Relatórios</h1>
            <div class="text-muted">Relatórios por período</div>
        </div>
    </div>

    <div class="card p-3">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Período</th>
                                                <th>Upload</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                        <tr>
                            <td>{{ $batch->display_label }}</td>
                                                        <td>{{ $batch->parsed_at ? $batch->parsed_at->format('d/m/Y H:i') : '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('reports.show', $batch) }}" class="btn btn-sm btn-outline-primary">Abrir</a>
                                <a href="{{ route('reports.pdf', $batch) }}" class="btn btn-sm btn-outline-secondary">PDF</a>
                            </td>
                        </tr>
                    @endforeach
                    @if($batches->isEmpty())
                        <tr>
                            <td colspan="3" class="text-center text-muted">Nenhum relatório disponível.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

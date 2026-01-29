<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Uploads</h1>
            <div class="text-muted">Batches mensais de Meta Ads + CRM Vendas</div>
        </div>
        <a href="{{ route('uploads.create') }}" class="btn btn-primary">Gerar relatório</a>
    </div>

    <div class="card p-3">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Período</th>
                                                <th>Arquivos</th>
                        <th>Processado</th>
                        <th>Stats</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                        <tr>
                            <td>{{ $batch->display_label }}</td>
                                                        <td>
                                <div class="small text-muted">Meta: {{ $batch->meta_csv_path ? 'OK' : '—' }}</div>
                                <div class="small text-muted">CRM Vendas: {{ $batch->intelbras_xlsx_path ? 'OK' : '—' }}</div>
                            </td>
                            <td>{{ $batch->parsed_at ? $batch->parsed_at->format('d/m/Y H:i') : '—' }}</td>
                            <td class="small">
                                @if($batch->parse_stats)
                                    <div>Meta rows: {{ $batch->parse_stats['meta']['rows'] ?? 0 }}</div>
                                    <div>Leads: {{ $batch->parse_stats['intelbras']['rows'] ?? 0 }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('reports.show', $batch) }}" class="btn btn-sm btn-outline-primary">Ver relatório</a>
                                <form action="{{ route('uploads.destroy', $batch) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir este relatório e os logs de upload?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($batches->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center text-muted">Nenhum upload encontrado.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

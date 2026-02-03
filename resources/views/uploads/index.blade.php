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
                        <th>Status</th>
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
                            <td>
                                <div class="d-flex flex-column gap-2" data-upload-status data-url="{{ route('uploads.status', $batch) }}">
                                    <div class="badge bg-{{ $batch->status === 'done' ? 'success' : ($batch->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ $batch->status ?? 'pending' }}
                                    </div>
                                    @if(in_array($batch->status, ['pending','processing']))
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $batch->progress ?? 0 }}%"></div>
                                        </div>
                                        <div class="small text-muted">{{ $batch->progress ?? 0 }}%</div>
                                    @elseif($batch->status === 'failed')
                                        <div class="small text-danger">Falha no processamento</div>
                                    @endif
                                </div>
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
                            <td colspan="6" class="text-center text-muted">Nenhum upload encontrado.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const pollUploadStatus = () => {
            document.querySelectorAll('[data-upload-status]').forEach((el) => {
                const url = el.getAttribute('data-url');
                if (!url) return;

                fetch(url)
                    .then((res) => res.json())
                    .then((data) => {
                        if (!data || !data.status) return;
                        const badge = el.querySelector('.badge');
                        const progressBar = el.querySelector('.progress-bar');
                        const progressText = el.querySelector('.small');

                        if (badge) {
                            badge.textContent = data.status;
                            badge.classList.remove('bg-success', 'bg-danger', 'bg-warning');
                            badge.classList.add(data.status === 'done' ? 'bg-success' : (data.status === 'failed' ? 'bg-danger' : 'bg-warning'));
                        }

                        if (progressBar) {
                            progressBar.style.width = `${data.progress ?? 0}%`;
                        }
                        if (progressText && typeof data.progress !== 'undefined') {
                            progressText.textContent = `${data.progress}%`;
                        }
                    });
            });
        };

        pollUploadStatus();
        setInterval(pollUploadStatus, 5000);
    </script>
</x-app-layout>

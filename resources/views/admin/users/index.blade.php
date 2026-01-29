<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Empresas</h1>
            <div class="text-muted">Cadastros de acesso ao painel</div>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Nova empresa</a>
    </div>

    <div class="card p-3">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ $user->status }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <a href="{{ route('admin.users.impersonate', $user) }}" class="btn btn-sm btn-outline-success">Acessar painel</a>
                                @if($user->status === 'active' && $user->id !== auth()->id())
                                    <form action="{{ route('admin.users.deactivate', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Desativar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if($users->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center text-muted">Nenhuma empresa cadastrada.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

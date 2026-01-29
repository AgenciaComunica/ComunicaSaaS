<x-app-layout>
    <div class="mb-4">
        <h1 class="h4 mb-1">Editar empresa</h1>
        <div class="text-muted">{{ $user->name }}</div>
    </div>

    <div class="card p-4">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome da empresa</label>
                    <input class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="active" @selected($user->status === 'active')>Ativo</option>
                        <option value="inactive" @selected($user->status === 'inactive')>Inativo</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nova senha</label>
                    <input class="form-control" type="password" name="password">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Confirmar senha</label>
                    <input class="form-control" type="password" name="password_confirmation">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Salvar</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Voltar</a>
            </div>
        </form>
    </div>
</x-app-layout>

<x-app-layout>
    <div class="mb-4">
        <h1 class="h4 mb-1">Novo usuário</h1>
        <div class="text-muted">Crie acesso ao painel</div>
    </div>

    <div class="card p-4">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome</label>
                    <input class="form-control" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="user">Usuário</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="active">Ativo</option>
                        <option value="inactive">Inativo</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Senha</label>
                    <input class="form-control" type="password" name="password" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Confirmar senha</label>
                    <input class="form-control" type="password" name="password_confirmation" required>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Salvar</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Voltar</a>
            </div>
        </form>
    </div>
</x-app-layout>

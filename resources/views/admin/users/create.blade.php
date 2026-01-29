<x-app-layout>
    <div class="mb-4">
        <h1 class="h4 mb-1">Nova empresa</h1>
        <div class="text-muted">Cadastro de acesso</div>
    </div>

    <div class="card p-4">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome da empresa</label>
                    <input class="form-control" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
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

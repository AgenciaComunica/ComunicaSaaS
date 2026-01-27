<x-guest-layout>
    <div class="mb-3 text-muted">
        Informe seu email para receber o link de redefinição de senha.
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="d-grid">
            <button class="btn btn-primary" type="submit">Enviar link</button>
        </div>
    </form>
</x-guest-layout>

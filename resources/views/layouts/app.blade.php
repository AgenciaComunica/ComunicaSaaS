<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background-color: #f5f7fb; }
            .navbar-brand { font-weight: 600; letter-spacing: .3px; }
            .card { border: 0; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
            .table thead th { background: #f0f2f6; }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg bg-white border-bottom">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('dashboard') }}">Comunica SaaS</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('uploads.index') }}">Uploads</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('reports.index') }}">Relatórios</a></li>
                        @if(auth()->user()?->isAdmin())
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.index') }}">Usuários</a></li>
                        @endif
                    </ul>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted small">{{ auth()->user()->name ?? '' }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-secondary btn-sm" type="submit">Sair</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <main class="container py-4">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{ $slot }}
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>

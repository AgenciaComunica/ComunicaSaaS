<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            :root {
                --brand-teal: #009696;
                --brand-gray: #5e5f5f;
                --brand-light: #e6e7e8;
            }
            body { background-color: var(--brand-light); }
            .navbar { background: #ffffff; }
            .navbar-brand { font-weight: 600; letter-spacing: .3px; color: var(--brand-gray); }
            .btn-primary { background-color: var(--brand-teal); border-color: var(--brand-teal); }
            .btn-primary:hover { background-color: #007d7d; border-color: #007d7d; }
            .card { border: 0; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
            .table thead th { background: #f0f2f6; }
            .offcanvas-nav .nav-link {
                color: var(--brand-gray);
                border-radius: 10px;
                padding: 10px 12px;
                transition: all .2s ease;
            }
            .offcanvas-nav .nav-link:hover {
                background: rgba(0, 150, 150, 0.12);
                color: var(--brand-teal);
                transform: translateX(4px);
            }
            .offcanvas-nav .nav-link.active {
                color: var(--brand-teal);
                font-weight: 600;
                background: rgba(0, 150, 150, 0.16);
            }
            .btn-logout {
                border-color: #dc3545;
                color: #dc3545;
                font-weight: 600;
            }
            .btn-logout:hover {
                background: #dc3545;
                color: #fff;
            }
        </style>
    </head>
    <body>
        <nav class="navbar bg-white border-bottom">
            <div class="container-fluid justify-content-between">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#mainMenu" aria-controls="mainMenu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand mx-auto" href="{{ route('reports.index') }}">
                    <img src="{{ asset('img/Nova logo Comunica 2020.png') }}" alt="Comunica" height="80">
                </a>
                <span style="width:44px"></span>
            </div>
        </nav>

        <div class="offcanvas offcanvas-start" tabindex="-1" id="mainMenu" aria-labelledby="mainMenuLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="mainMenuLabel">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body offcanvas-nav">
                <div class="mb-3">
                    <div class="text-uppercase small text-muted">Empresa</div>
                    <div class="h5 mb-0">{{ auth()->user()->name ?? '' }}</div>
                </div>
                <ul class="nav flex-column gap-1">
                    @if(auth()->user()?->isAdmin())
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.index') }}">Empresas</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('reports.index') }}">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('uploads.index') }}">Uploads</a></li>
                    @endif
                </ul>

                <div class="mt-4 pt-3 border-top">
                    @if(session('impersonator_id'))
                        <a href="{{ route('impersonate.stop') }}" class="btn btn-outline-warning btn-sm mt-2">Voltar ao Admin</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button class="btn btn-logout btn-sm w-100" type="submit">Sair</button>
                    </form>
                </div>
            </div>
        </div>

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

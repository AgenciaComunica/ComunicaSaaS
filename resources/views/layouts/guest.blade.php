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
            body { background: linear-gradient(120deg, var(--brand-light), #f2f2f2); }
            .card { border: 0; box-shadow: 0 12px 28px rgba(0,0,0,.08); }
            .btn-primary { background-color: var(--brand-teal); border-color: var(--brand-teal); }
            .btn-primary:hover { background-color: #007d7d; border-color: #007d7d; }
        </style>
    </head>
    <body>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/Nova logo Comunica 2020.png') }}" alt="Comunica" height="165" class="mb-2">
                    </div>
                    <div class="card p-4">
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
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>

{{-- Minimal chrome for redemption + error pages: no session/auth dependencies,
     so it renders safely even when the app is degraded (500/503). --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="otl-status-page d-flex flex-column min-vh-100">
<main class="flex-grow-1 d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card otl-card text-center @yield('card-class')">
                    <div class="card-body p-4 p-md-5">
                        @yield('content')
                    </div>
                </div>
                <p class="text-center mt-4 mb-0">
                    <a href="{{ url('/') }}" class="otl-muted-link">← {{ config('app.name') }}</a>
                </p>
            </div>
        </div>
    </div>
</main>
</body>
</html>

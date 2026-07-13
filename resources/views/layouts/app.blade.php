<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'One-time links') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg otl-nav">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('home') }}">
            <span class="otl-brand-mark">⤳</span> {{ config('app.name') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"
                aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('links.create') }}">New link</a></li>
                @auth
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('links.index') }}">My links</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('referrals.index') }}">Referrals</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('wallet.index') }}">Wallet</a></li>
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item"><a class="nav-link text-warning" href="{{ route('admin.dashboard') }}">Admin</a></li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav">
                @auth
                    <li class="nav-item me-lg-3">
                        <span class="nav-link">
                            <span class="badge otl-points">{{ auth()->user()->points_balance }} pts</span>
                        </span>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('profile.edit') }}">{{ auth()->user()->name }}</a></li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link">Sign out</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Sign in</a></li>
                    <li class="nav-item"><a class="btn btn-otl ms-lg-2" href="{{ route('register') }}">Create account</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="flex-grow-1 py-4">
    <div class="container">
        @if ($announcement = \App\Models\Setting::get('site_announcement'))
            <div class="alert otl-announce" role="status">{{ $announcement }}</div>
        @endif

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</main>

<footer class="otl-footer py-3 mt-auto">
    <div class="container d-flex flex-wrap justify-content-between small">
        <span>&copy; {{ date('Y') }} {{ config('app.name') }}</span>
        <span>Every link opens once. Then it's gone.</span>
    </div>
</footer>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
    <div id="otl-toast" class="toast align-items-center" role="status" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="otl-toast-body"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>

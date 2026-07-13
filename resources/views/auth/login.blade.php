@extends('layouts.app')
@section('title', 'Sign in')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card otl-card">
            <div class="card-body p-4">
                <h1 class="h4 mb-4">Sign in</h1>
                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" required autofocus autocomplete="email"
                               value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Keep me signed in</label>
                    </div>
                    <button type="submit" class="btn btn-otl w-100">Sign in</button>
                </form>
                <div class="d-flex justify-content-between mt-3 small">
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                    <a href="{{ route('register') }}">Create account</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

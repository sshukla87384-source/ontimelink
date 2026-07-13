@extends('layouts.app')
@section('title', 'Create account')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card otl-card">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Create your account</h1>
                <p class="text-secondary small mb-4">Includes 10 free points - one point per link.</p>
                @if (session('referral_code'))
                    <div class="alert alert-info py-2 small">Referral code <strong>{{ session('referral_code') }}</strong> applied - you'll receive {{ config('onetimelink.points.referred_bonus') }} bonus points after email verification.</div>
                @endif
                <form method="POST" action="{{ route('register') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" name="name" type="text" required autofocus autocomplete="name"
                               value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" required autocomplete="email"
                               value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               autocomplete="new-password" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-otl w-100">Create account</button>
                </form>
                <p class="small mt-3 mb-0">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
            </div>
        </div>
    </div>
</div>
@endsection

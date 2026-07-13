@extends('layouts.app')
@section('title', 'Reset password')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card otl-card">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Reset your password</h1>
                <p class="text-secondary small">Enter your account email and we'll send a reset link.</p>
                <form method="POST" action="{{ route('password.email') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" required autofocus
                               value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-otl w-100">Send reset link</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

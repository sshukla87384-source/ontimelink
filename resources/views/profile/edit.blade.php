@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <p class="otl-eyebrow mb-1">Account</p>
            <h1 class="h3 mb-4">Profile &amp; security</h1>

            <div class="card otl-card mb-4">
                <div class="card-header">Profile</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                   name="name" value="{{ old('name', $user->name) }}" required maxlength="100">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                            <div class="form-text">Email changes require support so account history stays verifiable.</div>
                        </div>
                        <button type="submit" class="btn btn-otl">Save changes</button>
                    </form>
                </div>
            </div>

            <div class="card otl-card mb-4">
                <div class="card-header">Change password</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current password</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password" name="current_password" required autocomplete="current-password">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm new password</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                   name="password_confirmation" required autocomplete="new-password">
                        </div>
                        <p class="small text-secondary">Changing your password signs you out of every other device.</p>
                        <button type="submit" class="btn btn-otl">Update password</button>
                    </form>
                </div>
            </div>

            <div class="card otl-card">
                <div class="card-header">Account</div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Account ID</span><span class="font-monospace">{{ $user->uuid }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Status</span><span class="text-capitalize">{{ $user->status }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Member since</span><span>{{ $user->created_at->format('F j, Y') }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-secondary">Email verified</span><span>{{ $user->email_verified_at?->format('F j, Y') ?? 'Not verified' }}</span></div>
                </div>
            </div>
        </div>
    </div>
@endsection

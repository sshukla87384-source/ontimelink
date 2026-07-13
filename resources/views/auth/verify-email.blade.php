@extends('layouts.app')
@section('title', 'Verify your email')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card otl-card">
            <div class="card-body p-4 text-center">
                <h1 class="h4 mb-3">Check your inbox</h1>
                <p class="text-secondary">We sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
                   Verifying unlocks link generation@if(session('referral_code') || auth()->user()->referred_by) and your referral bonus @endif.</p>
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">Resend verification email</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

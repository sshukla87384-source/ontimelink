@extends('layouts.app')
@section('title', 'Your link is ready')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card otl-card otl-burn-edge">
            <div class="card-body p-4">
                <h1 class="h4 mb-2">Your one-time link is ready</h1>
                <div class="alert alert-warning small">
                    <strong>Copy it now.</strong> For security, the link is shown only this once -
                    we store an irreversible hash, so it cannot be recovered later.
                </div>
                <div class="input-group input-group-lg mb-3">
                    <input type="text" readonly class="form-control font-monospace" id="redeem-url"
                           value="{{ $redeemUrl }}" aria-label="One-time link">
                    <button class="btn btn-otl" type="button" data-copy="#redeem-url">Copy</button>
                </div>
                <dl class="row small text-secondary mb-4">
                    <dt class="col-4 col-sm-3">Status</dt><dd class="col-8 col-sm-9">Active - opens once, then locks</dd>
                    <dt class="col-4 col-sm-3">Expires</dt><dd class="col-8 col-sm-9">{{ $link->expires_at?->toDayDateTimeString() ?? 'Never (until redeemed)' }}</dd>
                    @if ($link->label)
                        <dt class="col-4 col-sm-3">Label</dt><dd class="col-8 col-sm-9">{{ $link->label }}</dd>
                    @endif
                </dl>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('links.create') }}" class="btn btn-outline-secondary">Create another</a>
                    @auth<a href="{{ route('links.index') }}" class="btn btn-outline-secondary">My links</a>@endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Create a one-time link')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card otl-card">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Create a one-time link</h1>
                @guest
                    <p class="text-secondary small mb-4">Guests get one free link. <a href="{{ route('register') }}">Create an account</a> for 10 free points.</p>
                @else
                    <p class="text-secondary small mb-4">Costs {{ config('onetimelink.points.cost_per_link') }} point · you have <strong>{{ auth()->user()->points_balance }}</strong>.</p>
                @endguest

                @if ($guestBlocked ?? false)
                    <div class="alert alert-warning">
                        Your free guest link has been used.
                        <a href="{{ route('register') }}" class="alert-link">Register</a> to keep going - new accounts start with 10 points.
                    </div>
                @else
                    <form method="POST" action="{{ route('links.store') }}" id="create-form" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="destination" class="form-label">Destination URL</label>
                            <input id="destination" name="destination" type="url" required inputmode="url"
                                   placeholder="https://example.com/secret-page"
                                   value="{{ old('destination') }}"
                                   class="form-control form-control-lg @error('destination') is-invalid @enderror">
                            @error('destination')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Encrypted before storage. Never shown to anyone but the first visitor.</div>
                        </div>
                        <div class="row">
                            <div class="col-sm-7 mb-3">
                                <label for="label" class="form-label">Label <span class="text-secondary">(only you see this)</span></label>
                                <input id="label" name="label" type="text" maxlength="120" value="{{ old('label') }}"
                                       class="form-control @error('label') is-invalid @enderror">
                                @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-5 mb-3">
                                <label for="expires_in_days" class="form-label">Expires after</label>
                                <select id="expires_in_days" name="expires_in_days" class="form-select">
                                    <option value="">Never (until redeemed)</option>
                                    @foreach ([1 => '1 day', 7 => '7 days', 30 => '30 days', 90 => '90 days'] as $days => $labelText)
                                        <option value="{{ $days }}" @selected(old('expires_in_days') == $days)>{{ $labelText }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-otl btn-lg w-100" data-loading-text="Encrypting…">
                            Generate one-time link
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

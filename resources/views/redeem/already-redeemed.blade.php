@extends('layouts.minimal')

@section('title', 'Already redeemed')
@section('card-class', 'otl-burn-edge')

@section('content')
    <div class="otl-status-glyph" aria-hidden="true">✦</div>
    <p class="otl-eyebrow mb-2">One-time link</p>
    <h1 class="h3 mb-3">Already redeemed</h1>
    <p class="text-secondary mb-1">
        This link was opened once — and once is all it gets. The destination
        was permanently sealed the moment it was first visited.
    </p>
    @if (! empty($redeemedAt))
        <p class="small text-secondary mb-0">Redeemed {{ $redeemedAt->diffForHumans() }} ({{ $redeemedAt->toDayDateTimeString() }} UTC).</p>
    @endif
    <hr class="my-4">
    <p class="small text-secondary mb-3">Need to share something again? Generate a fresh one-time link.</p>
    <a href="{{ route('links.create') }}" class="btn btn-otl">Create a new link</a>
@endsection

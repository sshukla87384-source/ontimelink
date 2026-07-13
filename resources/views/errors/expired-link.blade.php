@extends('layouts.minimal')

@section('title', 'Link expired')

@section('content')
    <div class="otl-status-glyph" aria-hidden="true">⧖</div>
    <p class="otl-eyebrow mb-2">One-time link</p>
    <h1 class="h3 mb-3">This link has expired</h1>
    <p class="text-secondary mb-0">
        The creator set an expiry on this link and its window has closed
        before anyone redeemed it. The destination is no longer reachable.
    </p>
    <hr class="my-4">
    <a href="{{ route('links.create') }}" class="btn btn-otl">Create a new link</a>
@endsection

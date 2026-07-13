@extends('layouts.minimal')

@section('title', 'Invalid link')

@section('content')
    <div class="otl-status-glyph" aria-hidden="true">?</div>
    <p class="otl-eyebrow mb-2">One-time link</p>
    <h1 class="h3 mb-3">Invalid link</h1>
    <p class="text-secondary mb-0">
        This redemption link doesn't exist. It may have been mistyped,
        truncated when copied, or it was never issued by this service.
    </p>
    <hr class="my-4">
    <a href="{{ route('links.create') }}" class="btn btn-otl">Create a link</a>
@endsection

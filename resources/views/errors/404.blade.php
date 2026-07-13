@extends('layouts.minimal')

@section('title', '404 — Not found')

@section('content')
    <div class="otl-status-glyph" aria-hidden="true">404</div>
    <h1 class="h3 mb-3">Page not found</h1>
    <p class="text-secondary mb-0">The page you're looking for doesn't exist or has moved.</p>
    <hr class="my-4">
    <a href="{{ url('/') }}" class="btn btn-otl">Back to home</a>
@endsection

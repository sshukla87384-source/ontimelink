@extends('layouts.minimal')

@section('title', '403 — Forbidden')

@section('content')
    <div class="otl-status-glyph" aria-hidden="true">403</div>
    <h1 class="h3 mb-3">Access denied</h1>
    <p class="text-secondary mb-0">{{ $exception->getMessage() ?: "You don't have permission to view this page." }}</p>
    <hr class="my-4">
    <a href="{{ url('/') }}" class="btn btn-otl">Back to home</a>
@endsection

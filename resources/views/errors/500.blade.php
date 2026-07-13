@extends('layouts.minimal')

@section('title', '500 — Server error')

@section('content')
    <div class="otl-status-glyph" aria-hidden="true">500</div>
    <h1 class="h3 mb-3">Something went wrong</h1>
    <p class="text-secondary mb-0">An unexpected error occurred on our side. It has been logged — please try again shortly.</p>
    <hr class="my-4">
    <a href="{{ url('/') }}" class="btn btn-otl">Back to home</a>
@endsection

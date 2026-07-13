@extends('layouts.minimal')

@section('title', '419 — Session expired')

@section('content')
    <div class="otl-status-glyph" aria-hidden="true">419</div>
    <h1 class="h3 mb-3">Your session expired</h1>
    <p class="text-secondary mb-0">For your security this form timed out. Go back, refresh the page, and try again.</p>
    <hr class="my-4">
    <a href="{{ url()->previous('/') }}" class="btn btn-otl">Go back</a>
@endsection

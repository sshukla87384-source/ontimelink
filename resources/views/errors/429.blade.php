@extends('layouts.minimal')

@section('title', '429 — Slow down')

@section('content')
    <div class="otl-status-glyph" aria-hidden="true">429</div>
    <h1 class="h3 mb-3">Too many requests</h1>
    <p class="text-secondary mb-0">You're moving faster than we allow. Wait a minute, then try again.</p>
    <hr class="my-4">
    <a href="{{ url('/') }}" class="btn btn-otl">Back to home</a>
@endsection

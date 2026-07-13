@extends('layouts.app')

@section('title', 'Share a link that self-destructs')

@section('content')
<div class="row align-items-center py-5 otl-hero">
    <div class="col-lg-6">
        <p class="otl-eyebrow mb-2">One-time redemption links</p>
        <h1 class="display-5 fw-bold mb-3">The link works once.<br>Then it burns.</h1>
        <p class="lead mb-4">
            Paste any URL and get back an encrypted, single-use link. The first person
            to open it gets through; everyone after sees <em>Already Redeemed</em> - permanently.
        </p>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('links.create') }}" class="btn btn-otl btn-lg">Create your free link</a>
            <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg">Get 10 free points</a>
        </div>
    </div>
    <div class="col-lg-6 mt-5 mt-lg-0">
        <div class="card otl-card">
            <div class="card-body p-4">
                <h2 class="h6 text-uppercase otl-eyebrow">How it works</h2>
                <ol class="otl-steps mb-0">
                    <li><strong>Paste a URL.</strong> It's encrypted with AES-256 before it ever touches the database.</li>
                    <li><strong>Share the link.</strong> A 64-character random token no one can guess.</li>
                    <li><strong>First open wins.</strong> Redemption is atomic - two simultaneous clicks can't both get in.</li>
                    <li><strong>Gone for good.</strong> Every later visit shows "Already Redeemed."</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

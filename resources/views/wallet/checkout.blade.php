@extends('layouts.app')

@section('title', 'Complete payment')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <p class="otl-eyebrow mb-1">Checkout</p>
            <h1 class="h3 mb-4">Complete your payment</h1>

            <div class="card otl-card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Points</span>
                        <strong>{{ number_format($payment->points_purchased) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Amount due</span>
                        <strong>${{ number_format($payment->amount / 100, 2) }} {{ $payment->currency }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Method</span>
                        <strong class="text-capitalize">{{ $payment->gateway }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary">Order ID</span>
                        <span class="font-monospace small">{{ $payment->uuid }}</span>
                    </div>
                </div>
            </div>

            <div class="card otl-card mb-4">
                <div class="card-header">Payment instructions</div>
                <div class="card-body">
                    @if (($instructions['type'] ?? '') === 'crypto')
                        <p class="small text-secondary">Send the exact amount below and include the payment reference in the transaction memo so we can match it automatically.</p>
                    @else
                        <p class="small text-secondary">Open your WalletPay app, pay the merchant below, and quote the payment reference.</p>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Merchant ID</span>
                            <span class="font-monospace">{{ $instructions['merchant_id'] ?? '' }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Amount</span>
                        <strong>{{ $instructions['amount'] }} {{ $instructions['currency'] }}</strong>
                    </div>
                    <div class="mb-1 text-secondary small">Payment reference</div>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control font-monospace" id="pay-ref" value="{{ $instructions['reference'] }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" data-copy="#pay-ref">Copy</button>
                    </div>
                </div>
            </div>

            <div class="alert alert-info small">
                Your points are credited automatically the moment the payment processor confirms the transfer —
                usually within a few minutes. You can safely leave this page.
            </div>

            <a href="{{ route('wallet.index') }}" class="btn btn-outline-secondary">Back to wallet</a>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Wallet')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <p class="otl-eyebrow mb-1">Wallet</p>
            <h1 class="h3 mb-0">Balance &amp; top-ups</h1>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card otl-card otl-stat mb-4">
                <div class="card-body">
                    <div class="otl-stat-label">Available balance</div>
                    <div class="otl-stat-value">${{ number_format($wallet->balance / 100, 2) }} <span class="fs-6 text-secondary">{{ $wallet->currency }}</span></div>
                    @if ($wallet->is_frozen)
                        <span class="badge text-bg-warning mt-2">Wallet frozen — contact support</span>
                    @endif
                </div>
            </div>

            <div class="card otl-card">
                <div class="card-header">Buy points</div>
                <div class="card-body">
                    <p class="small text-secondary">1 point = 1 one-time link. Points are credited automatically once your payment is confirmed.</p>
                    <form method="POST" action="{{ route('wallet.purchase') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="points" class="form-label">Points to buy</label>
                            <input type="number" min="10" max="100000" step="1" class="form-control @error('points') is-invalid @enderror"
                                   id="points" name="points" value="{{ old('points', 100) }}" required>
                            @error('points')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Minimum 10 points · $0.10 per point.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label d-block">Payment method</label>
                            @forelse ($gateways as $key => $gateway)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gateway" id="gw-{{ $key }}"
                                           value="{{ $key }}" @checked(old('gateway', array_key_first($gateways)) === $key) required>
                                    <label class="form-check-label" for="gw-{{ $key }}">{{ $gateway->displayName() }}</label>
                                </div>
                            @empty
                                <p class="text-secondary small mb-0">No payment methods are enabled right now.</p>
                            @endforelse
                            @error('gateway')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-otl" data-loading-text="Preparing checkout…" @disabled(empty($gateways))>Continue to payment</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card otl-card mb-4">
                <div class="card-header">Wallet ledger</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Type</th><th>Amount</th><th>Balance after</th><th>Reference</th><th>When</th></tr></thead>
                        <tbody>
                        @forelse ($transactions as $tx)
                            <tr>
                                <td class="small text-capitalize">{{ str_replace('_', ' ', $tx->type) }}</td>
                                <td class="{{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $tx->amount >= 0 ? '+' : '-' }}${{ number_format(abs($tx->amount) / 100, 2) }}</td>
                                <td class="small">${{ number_format($tx->balance_after / 100, 2) }}</td>
                                <td class="small font-monospace text-secondary text-truncate" style="max-width: 140px">{{ $tx->reference_id }}</td>
                                <td class="small text-secondary text-nowrap">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-5">No wallet activity yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mb-4">{{ $transactions->links() }}</div>

            <div class="card otl-card">
                <div class="card-header">Recent payments</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Gateway</th><th>Amount</th><th>Points</th><th>Status</th><th>When</th></tr></thead>
                        <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="small text-capitalize">{{ $payment->gateway }}</td>
                                <td>${{ number_format($payment->amount / 100, 2) }}</td>
                                <td>{{ $payment->points_purchased }}</td>
                                <td><span class="badge otl-badge-{{ $payment->status === 'confirmed' ? 'active' : ($payment->status === 'pending' ? 'expired' : 'disabled') }}">{{ $payment->status }}</span></td>
                                <td class="small text-secondary text-nowrap">{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-5">No payments yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

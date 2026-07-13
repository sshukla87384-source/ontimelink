@extends('layouts.app')

@section('title', 'Admin · Payments')

@section('content')
    <p class="otl-eyebrow mb-1">Admin</p>
    <h1 class="h3 mb-4">Payments</h1>

    @include('admin._nav')

    <div class="card otl-card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Any</option>
                        @foreach (['pending', 'confirmed', 'failed', 'expired'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small" for="gateway">Gateway</label>
                    <select class="form-select" id="gateway" name="gateway">
                        <option value="">Any</option>
                        @foreach (['crypto', 'walletpay'] as $gateway)
                            <option value="{{ $gateway }}" @selected(request('gateway') === $gateway)>{{ ucfirst($gateway) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-otl">Filter</button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card otl-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Order</th><th>User</th><th>Gateway</th><th>Reference</th><th>Amount</th><th>Points</th><th>Status</th><th>Created</th></tr></thead>
                <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td class="small font-monospace text-truncate" style="max-width: 120px">{{ $payment->uuid }}</td>
                        <td class="small">{{ $payment->user?->email ?? '—' }}</td>
                        <td class="small text-capitalize">{{ $payment->gateway }}</td>
                        <td class="small font-monospace text-truncate" style="max-width: 130px">{{ $payment->gateway_reference }}</td>
                        <td>${{ number_format($payment->amount / 100, 2) }}</td>
                        <td>{{ number_format($payment->points_purchased) }}</td>
                        <td><span class="badge otl-badge-{{ $payment->status === 'confirmed' ? 'active' : ($payment->status === 'pending' ? 'expired' : 'disabled') }}">{{ $payment->status }}</span></td>
                        <td class="small text-secondary text-nowrap">{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-secondary py-5">No payments match these filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $payments->links() }}</div>
@endsection

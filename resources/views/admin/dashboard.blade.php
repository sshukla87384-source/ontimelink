@extends('layouts.app')

@section('title', 'Admin · Overview')

@section('content')
    <p class="otl-eyebrow mb-1">Admin</p>
    <h1 class="h3 mb-4">Platform overview</h1>

    @include('admin._nav')

    <div class="row g-3 mb-4">
        @foreach ([
            ['Users', number_format($stats['users']), '+'.$stats['users_today'].' today'],
            ['Links created', number_format($stats['links']), number_format($stats['links_active']).' active'],
            ['Links redeemed', number_format($stats['links_redeemed']), null],
            ['Referrals rewarded', number_format($stats['referrals_rewarded']), null],
            ['Confirmed revenue', '$'.number_format($stats['revenue_minor'] / 100, 2), null],
        ] as [$label, $value, $sub])
            <div class="col-6 col-md-4 col-xl">
                <div class="card otl-card otl-stat h-100">
                    <div class="card-body">
                        <div class="otl-stat-label">{{ $label }}</div>
                        <div class="otl-stat-value">{{ $value }}</div>
                        @if ($sub)<span class="small text-secondary">{{ $sub }}</span>@endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card otl-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Newest users</span>
                    <a class="otl-muted-link small" href="{{ route('admin.users.index') }}">All users</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Joined</th></tr></thead>
                        <tbody>
                        @forelse ($recentUsers as $user)
                            <tr>
                                <td><a class="otl-muted-link" href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></td>
                                <td class="small text-secondary">{{ $user->email }}</td>
                                <td><span class="badge otl-badge-{{ $user->status === 'active' ? 'active' : 'disabled' }}">{{ $user->status }}</span></td>
                                <td class="small text-secondary text-nowrap">{{ $user->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary py-4">No users yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card otl-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Latest payments</span>
                    <a class="otl-muted-link small" href="{{ route('admin.payments.index') }}">All payments</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>User</th><th>Gateway</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse ($recentPayments as $payment)
                            <tr>
                                <td class="small">{{ $payment->user?->email ?? '—' }}</td>
                                <td class="small text-capitalize">{{ $payment->gateway }}</td>
                                <td>${{ number_format($payment->amount / 100, 2) }}</td>
                                <td><span class="badge otl-badge-{{ $payment->status === 'confirmed' ? 'active' : ($payment->status === 'pending' ? 'expired' : 'disabled') }}">{{ $payment->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary py-4">No payments yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

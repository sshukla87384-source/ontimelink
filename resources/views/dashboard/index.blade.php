@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <p class="otl-eyebrow mb-1">Dashboard</p>
            <h1 class="h3 mb-0">Welcome back, {{ $user->name }}</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('links.create') }}" class="btn btn-otl">New link</a>
            <a href="{{ route('links.bulk') }}" class="btn btn-outline-secondary">Bulk generate</a>
        </div>
    </div>

    @if ($user->status !== \App\Models\User::STATUS_ACTIVE)
        <div class="alert alert-warning">
            Your account is currently <strong>{{ $user->status }}</strong>. Some actions are unavailable.
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card otl-card otl-stat h-100">
                <div class="card-body">
                    <div class="otl-stat-label">Points</div>
                    <div class="otl-stat-value">{{ number_format($user->points_balance) }}</div>
                    <a class="otl-muted-link small" href="{{ route('points.index') }}">Ledger →</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card otl-card otl-stat h-100">
                <div class="card-body">
                    <div class="otl-stat-label">Wallet</div>
                    <div class="otl-stat-value">${{ number_format($user->wallet->balance / 100, 2) }}</div>
                    <a class="otl-muted-link small" href="{{ route('wallet.index') }}">Wallet →</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card otl-card otl-stat h-100">
                <div class="card-body">
                    <div class="otl-stat-label">Active links</div>
                    <div class="otl-stat-value">{{ number_format($linkStats->active ?? 0) }}</div>
                    <span class="small text-secondary">{{ number_format($linkStats->total ?? 0) }} created total</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card otl-card otl-stat h-100">
                <div class="card-body">
                    <div class="otl-stat-label">Redeemed</div>
                    <div class="otl-stat-value">{{ number_format($linkStats->redeemed ?? 0) }}</div>
                    <a class="otl-muted-link small" href="{{ route('links.index') }}">My links →</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card otl-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent links</span>
                    <a class="otl-muted-link small" href="{{ route('links.index') }}">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Label</th><th>Status</th><th>Created</th><th>Expires</th></tr></thead>
                        <tbody>
                        @forelse ($recentLinks as $link)
                            <tr>
                                <td class="text-truncate" style="max-width: 180px">{{ $link->label ?: 'Untitled link' }}</td>
                                <td><span class="badge otl-badge-{{ $link->status }}">{{ $link->status }}</span></td>
                                <td class="small text-secondary">{{ $link->created_at->diffForHumans() }}</td>
                                <td class="small text-secondary">{{ $link->expires_at?->diffForHumans() ?? 'Never' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary py-4">No links yet — create your first one.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card otl-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent point activity</span>
                    <a class="otl-muted-link small" href="{{ route('points.index') }}">Full ledger</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Type</th><th>Amount</th><th>Balance</th><th>When</th></tr></thead>
                        <tbody>
                        @forelse ($recentPoints as $tx)
                            <tr>
                                <td class="small">{{ str_replace('_', ' ', $tx->type) }}</td>
                                <td class="{{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $tx->amount >= 0 ? '+' : '' }}{{ $tx->amount }}</td>
                                <td class="small text-secondary">{{ $tx->balance_after }}</td>
                                <td class="small text-secondary">{{ $tx->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary py-4">No point activity yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card otl-card mb-4">
                <div class="card-header">Referral program</div>
                <div class="card-body">
                    <p class="small text-secondary mb-2">
                        Share your link — you earn <strong>{{ config('onetimelink.points.referrer_reward') }} points</strong>
                        per verified sign-up, they get <strong>{{ config('onetimelink.points.referred_bonus') }}</strong>.
                    </p>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" class="form-control font-monospace" value="{{ $user->referralUrl() }}" readonly id="ref-url">
                        <button class="btn btn-outline-secondary" type="button" data-copy="#ref-url">Copy</button>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-secondary">Earned so far</span>
                        <strong>{{ number_format($referralEarnings) }} pts</strong>
                    </div>
                    <a class="otl-muted-link small" href="{{ route('referrals.index') }}">Referral history →</a>
                </div>
            </div>

            <div class="card otl-card">
                <div class="card-header">Activity timeline</div>
                <ul class="list-group list-group-flush otl-timeline">
                    @forelse ($recentActivity as $event)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span class="small">{{ str_replace(['.', '_'], [' · ', ' '], $event->event) }}</span>
                                <span class="small text-secondary text-nowrap">{{ $event->created_at->diffForHumans() }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-secondary py-4">No activity recorded yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

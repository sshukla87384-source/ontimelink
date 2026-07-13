@extends('layouts.app')

@section('title', 'Admin · '.$user->name)

@section('content')
    <p class="otl-eyebrow mb-1">Admin · Users</p>
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <h1 class="h3 mb-0">{{ $user->name }}
            @if ($user->isAdmin())<span class="badge text-bg-warning align-middle">admin</span>@endif
            @if ($user->trashed())<span class="badge text-bg-danger align-middle">deleted</span>@endif
        </h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">← All users</a>
    </div>

    @include('admin._nav')

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card otl-card mb-4">
                <div class="card-header">Account</div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between mb-2"><span class="text-secondary">UUID</span><span class="font-monospace">{{ $user->uuid }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Email</span><span>{{ $user->email }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Verified</span><span>{{ $user->email_verified_at?->format('Y-m-d') ?? 'No' }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Status</span><span class="text-capitalize">{{ $user->status }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Points</span><strong>{{ number_format($user->points_balance) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Wallet</span><strong>${{ number_format($user->wallet->balance / 100, 2) }} @if ($user->wallet->is_frozen)<span class="badge text-bg-warning">frozen</span>@endif</strong></div>
                    <div class="d-flex justify-content-between"><span class="text-secondary">Joined</span><span>{{ $user->created_at->format('Y-m-d H:i') }}</span></div>
                </div>
            </div>

            <div class="card otl-card mb-4">
                <div class="card-header">Account status</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.status', $user) }}" class="d-flex gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-select form-select-sm">
                            @foreach (['active' => 'Active', 'frozen' => 'Frozen (read-only)', 'banned' => 'Banned'] as $value => $label)
                                <option value="{{ $value }}" @selected($user->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-otl btn-sm">Apply</button>
                    </form>
                    @if ($user->trashed())
                        <form method="POST" action="{{ route('admin.users.restore', $user->uuid) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm w-100">Restore deleted account</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card otl-card mb-4">
                <div class="card-header">Adjust points</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.points', $user) }}">
                        @csrf
                        <div class="mb-2">
                            <input type="number" class="form-control form-control-sm" name="amount" placeholder="+50 or -50" required min="-100000" max="100000">
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm" name="reason" placeholder="Reason (audited)" required maxlength="255">
                        </div>
                        <button type="submit" class="btn btn-otl btn-sm">Adjust points</button>
                    </form>
                </div>
            </div>

            <div class="card otl-card">
                <div class="card-header">Adjust wallet</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.wallet', $user) }}">
                        @csrf
                        <div class="mb-2">
                            <input type="number" class="form-control form-control-sm" name="amount_minor" placeholder="Amount in cents, e.g. 500 or -500" required min="-10000000" max="10000000">
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm" name="reason" placeholder="Reason (audited)" required maxlength="255">
                        </div>
                        <button type="submit" class="btn btn-otl btn-sm">Adjust wallet</button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.wallet.freeze', $user) }}" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                            {{ $user->wallet->is_frozen ? 'Unfreeze wallet' : 'Freeze wallet' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card otl-card mb-4">
                <div class="card-header">Recent links</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Label</th><th>Status</th><th>Created</th><th>Redeemed</th></tr></thead>
                        <tbody>
                        @forelse ($links as $link)
                            <tr>
                                <td class="text-truncate" style="max-width: 200px">{{ $link->label ?: 'Untitled link' }}</td>
                                <td><span class="badge otl-badge-{{ $link->status }}">{{ $link->status }}</span></td>
                                <td class="small text-secondary">{{ $link->created_at->format('Y-m-d H:i') }}</td>
                                <td class="small text-secondary">{{ $link->redeemed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary py-4">No links.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card otl-card mb-4">
                <div class="card-header">Recent point transactions</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Type</th><th>Amount</th><th>Balance</th><th>Description</th><th>When</th></tr></thead>
                        <tbody>
                        @forelse ($pointTransactions as $tx)
                            <tr>
                                <td class="small text-capitalize">{{ str_replace('_', ' ', $tx->type) }}</td>
                                <td class="{{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $tx->amount >= 0 ? '+' : '' }}{{ $tx->amount }}</td>
                                <td class="small">{{ $tx->balance_after }}</td>
                                <td class="small text-secondary text-truncate" style="max-width: 220px">{{ $tx->description }}</td>
                                <td class="small text-secondary text-nowrap">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-4">No point activity.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card otl-card">
                <div class="card-header">Recent wallet transactions</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Type</th><th>Amount</th><th>Balance</th><th>Reference</th><th>When</th></tr></thead>
                        <tbody>
                        @forelse ($walletTransactions as $tx)
                            <tr>
                                <td class="small text-capitalize">{{ str_replace('_', ' ', $tx->type) }}</td>
                                <td class="{{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $tx->amount >= 0 ? '+' : '-' }}${{ number_format(abs($tx->amount) / 100, 2) }}</td>
                                <td class="small">${{ number_format($tx->balance_after / 100, 2) }}</td>
                                <td class="small font-monospace text-secondary text-truncate" style="max-width: 140px">{{ $tx->reference_id }}</td>
                                <td class="small text-secondary text-nowrap">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-4">No wallet activity.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

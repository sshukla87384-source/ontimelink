@extends('layouts.app')

@section('title', 'Referrals')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <p class="otl-eyebrow mb-1">Referrals</p>
            <h1 class="h3 mb-0">Invite &amp; earn</h1>
        </div>
        <span class="badge otl-points fs-6">{{ number_format($totalEarned) }} pts earned</span>
    </div>

    <div class="card otl-card mb-4">
        <div class="card-body">
            <p class="small text-secondary mb-2">
                Every friend who registers with your link <em>and verifies their email</em> earns you
                <strong>{{ config('onetimelink.points.referrer_reward') }} points</strong>; they start with
                <strong>{{ config('onetimelink.points.referred_bonus') }} points</strong> instead of the usual
                {{ config('onetimelink.points.signup_bonus') }}.
            </p>
            <div class="input-group">
                <input type="text" class="form-control font-monospace" id="ref-url" value="{{ $referralUrl }}" readonly>
                <button class="btn btn-otl" type="button" data-copy="#ref-url">Copy link</button>
            </div>
        </div>
    </div>

    <div class="card otl-card">
        <div class="card-header">Referral history</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>User</th><th>Joined</th><th>Status</th><th>Your reward</th></tr></thead>
                <tbody>
                @forelse ($referrals as $referral)
                    <tr>
                        <td>{{ $referral->referredUser?->name ?? 'Deleted account' }}</td>
                        <td class="small text-secondary">{{ $referral->created_at->format('Y-m-d') }}</td>
                        <td><span class="badge otl-badge-{{ $referral->status === 'rewarded' ? 'active' : ($referral->status === 'pending' ? 'expired' : 'disabled') }}">{{ $referral->status }}</span></td>
                        <td>{{ $referral->status === 'rewarded' ? '+'.$referral->referrer_points.' pts' : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-secondary py-5">No referrals yet — share your link to get started.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $referrals->links() }}</div>
@endsection

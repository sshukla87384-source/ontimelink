@extends('layouts.app')

@section('title', 'Admin · Referrals')

@section('content')
    <p class="otl-eyebrow mb-1">Admin</p>
    <h1 class="h3 mb-4">Referrals</h1>

    @include('admin._nav')

    <div class="card otl-card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.referrals.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Any</option>
                        @foreach (['pending', 'rewarded', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-otl">Filter</button>
                    <a href="{{ route('admin.referrals.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card otl-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Referrer</th><th>Referred user</th><th>Status</th><th>Referrer pts</th><th>Referred pts</th><th>Created</th></tr></thead>
                <tbody>
                @forelse ($referrals as $referral)
                    <tr>
                        <td class="small">{{ $referral->referrer?->email ?? '—' }}</td>
                        <td class="small">{{ $referral->referredUser?->email ?? '—' }}</td>
                        <td><span class="badge otl-badge-{{ $referral->status === 'rewarded' ? 'active' : ($referral->status === 'pending' ? 'expired' : 'disabled') }}">{{ $referral->status }}</span></td>
                        <td>{{ $referral->status === 'rewarded' ? '+'.$referral->referrer_points : '—' }}</td>
                        <td>{{ $referral->status === 'rewarded' ? '+'.$referral->referred_points : '—' }}</td>
                        <td class="small text-secondary text-nowrap">{{ $referral->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-secondary py-5">No referrals recorded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $referrals->links() }}</div>
@endsection

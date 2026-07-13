@extends('layouts.app')

@section('title', 'Point ledger')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <p class="otl-eyebrow mb-1">Points</p>
            <h1 class="h3 mb-0">Point ledger</h1>
        </div>
        <span class="badge otl-points fs-6">{{ number_format($balance) }} pts</span>
    </div>

    <div class="card otl-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Type</th><th>Amount</th><th>Balance after</th><th>Description</th><th>When</th></tr></thead>
                <tbody>
                @forelse ($transactions as $tx)
                    <tr>
                        <td class="small text-capitalize">{{ str_replace('_', ' ', $tx->type) }}</td>
                        <td class="{{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $tx->amount >= 0 ? '+' : '' }}{{ $tx->amount }}</td>
                        <td>{{ $tx->balance_after }}</td>
                        <td class="small text-secondary text-truncate" style="max-width: 320px">{{ $tx->description }}</td>
                        <td class="small text-secondary text-nowrap">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-secondary py-5">No transactions yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $transactions->links() }}</div>
@endsection

@extends('layouts.app')
@section('title', 'My links')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h4 mb-0">My links</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('links.bulk') }}" class="btn btn-outline-secondary btn-sm">Bulk generate</a>
        <a href="{{ route('links.create') }}" class="btn btn-otl btn-sm">New link</a>
    </div>
</div>

<form method="GET" class="mb-3">
    <div class="btn-group" role="group" aria-label="Filter by status">
        @foreach (['' => 'All', 'active' => 'Active', 'redeemed' => 'Redeemed', 'expired' => 'Expired', 'disabled' => 'Disabled'] as $value => $label)
            <a href="{{ route('links.index', $value ? ['status' => $value] : []) }}"
               class="btn btn-sm {{ request('status', '') === $value ? 'btn-otl' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>
</form>

<div class="card otl-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Label</th><th>Status</th><th>Created</th><th>Redeemed</th><th>Expires</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse ($links as $link)
                <tr>
                    <td>{{ $link->label ?? 'Untitled link' }}</td>
                    <td><span class="badge otl-badge-{{ $link->status }}">{{ ucfirst($link->status) }}</span></td>
                    <td class="text-secondary small">{{ $link->created_at->diffForHumans() }}</td>
                    <td class="text-secondary small">{{ $link->redeemed_at?->diffForHumans() ?? '—' }}</td>
                    <td class="text-secondary small">{{ $link->expires_at?->toFormattedDateString() ?? 'Never' }}</td>
                    <td class="text-end">
                        @if ($link->status === 'active')
                            <form method="POST" action="{{ route('links.disable', $link) }}" class="d-inline"
                                  onsubmit="return confirm('Disable this link? Nobody will be able to redeem it.')">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Disable</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-4">No links yet. <a href="{{ route('links.create') }}">Create your first one</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $links->links() }}</div>
@endsection

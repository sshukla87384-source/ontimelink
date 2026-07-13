@extends('layouts.app')

@section('title', 'Admin · Links')

@section('content')
    <p class="otl-eyebrow mb-1">Admin</p>
    <h1 class="h3 mb-4">Links</h1>

    @include('admin._nav')

    <div class="card otl-card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.links.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small" for="user">Owner email</label>
                    <input type="search" class="form-control" id="user" name="user" value="{{ request('user') }}" placeholder="user@example.com">
                </div>
                <div class="col-md-3">
                    <label class="form-label small" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Any</option>
                        @foreach (['active', 'redeemed', 'expired', 'disabled'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-otl">Filter</button>
                    <a href="{{ route('admin.links.index') }}" class="btn btn-outline-secondary">Reset</a>
                    <a href="{{ route('admin.links.export') }}" class="btn btn-outline-secondary ms-auto">Export CSV</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card otl-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Label</th><th>Owner</th><th>Status</th><th>Created</th><th>Redeemed</th><th>Expires</th><th></th></tr></thead>
                <tbody>
                @forelse ($links as $link)
                    <tr>
                        <td class="text-truncate" style="max-width: 180px">{{ $link->label ?: 'Untitled link' }}</td>
                        <td class="small text-secondary">{{ $link->user?->email ?? 'guest' }}</td>
                        <td><span class="badge otl-badge-{{ $link->status }}">{{ $link->status }}</span></td>
                        <td class="small text-secondary text-nowrap">{{ $link->created_at->format('Y-m-d H:i') }}</td>
                        <td class="small text-secondary text-nowrap">{{ $link->redeemed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td class="small text-secondary text-nowrap">{{ $link->expires_at?->format('Y-m-d H:i') ?? 'Never' }}</td>
                        <td class="text-end">
                            @if ($link->status === \App\Models\Link::STATUS_ACTIVE)
                                <form method="POST" action="{{ route('admin.links.disable', $link) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Disable</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-secondary py-5">No links match these filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $links->links() }}</div>
@endsection

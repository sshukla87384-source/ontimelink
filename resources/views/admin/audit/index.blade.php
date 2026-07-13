@extends('layouts.app')

@section('title', 'Admin · Audit log')

@section('content')
    <p class="otl-eyebrow mb-1">Admin</p>
    <h1 class="h3 mb-4">Audit, activity &amp; security logs</h1>

    @include('admin._nav')

    <div class="card otl-card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small" for="category">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">Any</option>
                        @foreach (['security', 'activity', 'admin'] as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ ucfirst($category) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small" for="event">Event contains</label>
                    <input type="search" class="form-control" id="event" name="event" value="{{ request('event') }}" placeholder="e.g. login">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-otl">Filter</button>
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card otl-card">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Event</th><th>Category</th><th>User</th><th>Context</th><th>When</th></tr></thead>
                <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="small font-monospace">{{ $log->event }}</td>
                        <td><span class="badge otl-badge-{{ $log->category === 'security' ? 'disabled' : 'active' }}">{{ $log->category }}</span></td>
                        <td class="small">{{ $log->user?->email ?? 'guest' }}</td>
                        <td class="small text-secondary text-truncate" style="max-width: 320px">{{ $log->context ? json_encode($log->context) : '—' }}</td>
                        <td class="small text-secondary text-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-secondary py-5">No log entries match these filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $logs->links() }}</div>
@endsection

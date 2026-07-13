@extends('layouts.app')

@section('title', 'Admin · Users')

@section('content')
    <p class="otl-eyebrow mb-1">Admin</p>
    <h1 class="h3 mb-4">Users</h1>

    @include('admin._nav')

    <div class="card otl-card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small" for="q">Search</label>
                    <input type="search" class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="Name or email">
                </div>
                <div class="col-md-3">
                    <label class="form-label small" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Any</option>
                        @foreach (['active', 'frozen', 'banned'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-otl">Filter</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Reset</a>
                    <a href="{{ route('admin.users.export') }}" class="btn btn-outline-secondary ms-auto">Export CSV</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card otl-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Points</th><th>Links</th><th>Joined</th></tr></thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td><a class="otl-muted-link" href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a>
                            @if ($user->isAdmin())<span class="badge text-bg-warning ms-1">admin</span>@endif
                        </td>
                        <td class="small text-secondary">{{ $user->email }}</td>
                        <td><span class="badge otl-badge-{{ $user->status === 'active' ? 'active' : 'disabled' }}">{{ $user->status }}</span></td>
                        <td>{{ number_format($user->points_balance) }}</td>
                        <td>{{ number_format($user->links_count) }}</td>
                        <td class="small text-secondary text-nowrap">{{ $user->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-secondary py-5">No users match these filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
@endsection

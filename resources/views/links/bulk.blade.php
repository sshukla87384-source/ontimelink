@extends('layouts.app')
@section('title', 'Bulk generate')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card otl-card">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Bulk generate links</h1>
                <p class="text-secondary small mb-4">
                    One URL per line, up to {{ config('onetimelink.links.bulk_max') }} at a time.
                    Each link costs {{ config('onetimelink.points.cost_per_link') }} point - you have <strong>{{ auth()->user()->points_balance }}</strong>.
                    Duplicates are skipped automatically.
                </p>
                <form method="POST" action="{{ route('links.bulk.store') }}" enctype="multipart/form-data" id="bulk-form" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="urls" class="form-label">URLs</label>
                        <textarea id="urls" name="urls" rows="8" class="form-control font-monospace @error('urls') is-invalid @enderror"
                                  placeholder="https://example.com/one&#10;https://example.com/two">{{ old('urls') }}</textarea>
                        @error('urls')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text"><span id="url-count">0</span> URLs entered</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-7 mb-3">
                            <label for="csv" class="form-label">Or upload a CSV <span class="text-secondary">(URLs in the first column)</span></label>
                            <input id="csv" name="csv" type="file" accept=".csv,.txt" class="form-control @error('csv') is-invalid @enderror">
                            @error('csv')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-5 mb-3">
                            <label for="expires_in_days" class="form-label">Expires after</label>
                            <select id="expires_in_days" name="expires_in_days" class="form-select">
                                <option value="">Never (until redeemed)</option>
                                @foreach ([1 => '1 day', 7 => '7 days', 30 => '30 days', 90 => '90 days'] as $days => $labelText)
                                    <option value="{{ $days }}">{{ $labelText }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-otl btn-lg w-100" data-loading-text="Generating…">Generate links</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    const area = document.getElementById('urls');
    const count = document.getElementById('url-count');
    const update = () => count.textContent = area.value.split('\n').map(s => s.trim()).filter(Boolean).length;
    area.addEventListener('input', update); update();
</script>
@endpush

@extends('layouts.app')
@section('title', 'Bulk results')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card otl-card otl-burn-edge mb-3">
            <div class="card-body p-4">
                <h1 class="h4 mb-2">{{ count($rows) }} {{ Str::plural('link', count($rows)) }} generated</h1>
                <div class="alert alert-warning small mb-3">
                    <strong>This is the only time these links are shown.</strong> Copy or download them now.
                </div>
                @if ($rows)
                    <button class="btn btn-otl btn-sm mb-3" type="button" id="copy-all">Copy all</button>
                    <a class="btn btn-outline-secondary btn-sm mb-3" download="one-time-links.csv"
                       href="data:text/csv;charset=utf-8,{{ rawurlencode("source_url,one_time_link\n".collect($rows)->map(fn ($r) => '"'.str_replace('"', '""', $r['url']).'","'.$r['redeemUrl'].'"')->implode("\n")) }}">Download CSV</a>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="result-table">
                            <thead><tr><th>#</th><th>Source</th><th>One-time link</th></tr></thead>
                            <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td>{{ $row['row'] }}</td>
                                    <td class="small text-truncate" style="max-width: 220px">{{ $row['url'] }}</td>
                                    <td class="font-monospace small otl-copy-cell">{{ $row['redeemUrl'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                @if (! empty($skipped))
                    <h2 class="h6 mt-3">Skipped rows</h2>
                    <ul class="small text-danger mb-0">
                        @foreach ($skipped as $row => $message)
                            <li>Row {{ $row }}: {{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <a href="{{ route('links.index') }}" class="btn btn-outline-secondary">Back to my links</a>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.getElementById('copy-all')?.addEventListener('click', () => {
        const text = [...document.querySelectorAll('.otl-copy-cell')].map(td => td.textContent.trim()).join('\n');
        navigator.clipboard.writeText(text).then(() => window.otlToast('All links copied.'));
    });
</script>
@endpush

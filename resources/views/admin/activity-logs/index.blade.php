{{-- Owner: Rian | Data: $logs (paginated, with user) --}}
@extends('layouts.app')
@section('title', 'Activity Logs')
@section('content')
<div class="page-head"><div><h1>Activity Logs</h1><p>Who did what, and when</p></div></div>
<div class="card-soft p-3 p-md-4">
    <form method="GET" class="row g-2 mb-3 no-print">
        <div class="col-md-4"><select name="module" class="form-select"><option value="">All modules</option>
            @foreach (['patients', 'appointments', 'treatment-records', 'charges', 'users'] as $m)<option value="{{ $m }}" @selected(request('module') === $m)>{{ ucfirst($m) }}</option>@endforeach</select></div>
        <div class="col-md-4 d-flex gap-2"><button class="btn btn-navy">Filter</button><a href="{{ route('admin.activity-logs.index') }}" class="btn btn-soft">Reset</a></div>
    </form>
    <div class="table-responsive">
        <table class="table table-soft align-middle mb-0">
            <thead><tr><th>When</th><th>User</th><th>Module</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
            <tbody>
            @forelse ($logs as $l)
                <tr><td class="text-nowrap">{{ $l->created_at->format('M d, Y g:i A') }}</td><td>{{ $l->user->name ?? 'System' }}</td><td>{{ $l->module }}</td><td>{{ $l->action }}</td><td>{{ $l->description }}</td><td>{{ $l->ip_address }}</td></tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No activity yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $logs])
</div>
@endsection
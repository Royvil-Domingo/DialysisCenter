{{-- Owner: Cheyenne | Data: $records (paginated, with patient) --}}
@extends('layouts.app')
@section('title', 'Treatment Records')
@section('content')
<div class="page-head">
    <div><h1>Treatment Records</h1><p>Completed dialysis sessions</p></div>
    <a href="{{ route('treatment-records.create') }}" class="btn btn-navy"><i class="bi bi-plus-lg"></i> Record Session</a>
</div>
<div class="card-soft p-3 p-md-4">
    <div class="table-responsive">
        <table class="table table-soft align-middle mb-0">
            <thead><tr><th>Date</th><th>Patient</th><th>Weight (pre → post)</th><th>Duration</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse ($records as $r)
                <tr>
                    <td>{{ $r->session_date->format('M d, Y') }}</td>
                    <td class="fw-semibold">{{ $r->patient->full_name }}</td>
                    <td>{{ $r->pre_weight_kg ?? '—' }} → {{ $r->post_weight_kg ?? '—' }} kg</td>
                    <td>{{ $r->duration_minutes ? $r->duration_minutes . ' min' : '—' }}</td>
                    <td>@include('partials.pill', ['status' => $r->status])</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('treatment-records.show', $r) }}" class="icon-btn"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('treatment-records.edit', $r) }}" class="icon-btn"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No treatment records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $records])
</div>
@endsection
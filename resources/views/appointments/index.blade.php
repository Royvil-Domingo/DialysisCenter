{{-- Owner: Dren | Data: $appointments (paginated) --}}
@extends('layouts.app')
@section('title', 'Appointments')
@section('content')
<div class="page-head">
    <div><h1>Appointments</h1><p>Scheduled dialysis visits</p></div>
    <a href="{{ route('appointments.create') }}" class="btn btn-navy"><i class="bi bi-plus-lg"></i> Schedule Appointment</a>
</div>

<div class="card-soft p-3 p-md-4">
    <form method="GET" class="row g-2 mb-3 no-print">
        <div class="col-md-4">
            <input type="date" name="date" value="{{ request('date') }}" class="form-control">
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach (['scheduled', 'completed', 'cancelled', 'no_show'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button class="btn btn-navy">Filter</button>
            <a href="{{ route('appointments.index') }}" class="btn btn-soft">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-soft align-middle mb-0">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Assign</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($appointments as $a)
                <tr>
                    <td class="fw-semibold">{{ $a->patient?->full_name ?? 'Unknown patient' }}</td>
                    <td>{{ $a->appointment_date?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ $a->appointment_time ? \Carbon\Carbon::parse($a->appointment_time)->format('g:i A') : '—' }}</td>
                    <td>{{ $a->staff?->name ?? 'Unassigned' }}</td>
                    <td>@include('partials.pill', ['status' => $a->status])</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('appointments.show', $a) }}" class="icon-btn" title="View"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('appointments.edit', $a) }}" class="icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No appointments found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.pagination', ['paginator' => $appointments])
</div>
@endsection

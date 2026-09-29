{{-- Owner: Dren | Data: $appointment (patient, staff, treatmentRecord) --}}
@extends('layouts.app')
@section('title', 'Appointment Details')
@section('content')
<div class="page-head">
    <div><h1>Appointment #{{ $appointment->id }}</h1><p>@include('partials.pill', ['status' => $appointment->status])</p></div>
    <div class="d-flex gap-2"><a href="{{ route('appointments.index') }}" class="btn btn-soft">Back</a><a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-navy"><i class="bi bi-pencil"></i> Edit</a></div>
</div>
<div class="card-soft p-4" style="max-width:720px">
    <dl class="row mb-0">
        <dt class="col-sm-4">Patient</dt><dd class="col-sm-8"><a href="{{ route('patients.show', $appointment->patient) }}">{{ $appointment->patient->full_name }}</a></dd>
        <dt class="col-sm-4">Date</dt><dd class="col-sm-8">{{ $appointment->appointment_date->format('l, M d, Y') }}</dd>
        <dt class="col-sm-4">Time</dt><dd class="col-sm-8">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</dd>
        <dt class="col-sm-4">Assigned Staff</dt><dd class="col-sm-8">{{ $appointment->staff->name ?? '—' }}</dd>
        <dt class="col-sm-4">Notes</dt><dd class="col-sm-8">{{ $appointment->notes ?: '—' }}</dd>
    </dl>
    <hr>
    @if ($appointment->treatmentRecord)
        <a href="{{ route('treatment-records.show', $appointment->treatmentRecord) }}" class="btn btn-soft"><i class="bi bi-clipboard2-pulse"></i> View treatment record</a>
    @else
        <a href="{{ route('treatment-records.create', ['appointment_id' => $appointment->id]) }}" class="btn btn-navy"><i class="bi bi-clipboard2-plus"></i> Record treatment session</a>
    @endif
</div>
@endsection
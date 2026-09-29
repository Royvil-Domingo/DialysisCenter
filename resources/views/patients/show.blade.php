{{-- Owner: Rian | Data: $patient (+appointments, charges loaded) --}}
@extends('layouts.app')
@section('title', $patient->full_name)
@section('content')
<div class="page-head">
    <div><h1>{{ $patient->full_name }}</h1><p>{{ $patient->patient_code }} · @include('partials.pill', ['status' => $patient->status])</p></div>
    <div class="d-flex gap-2"><a href="{{ route('patients.index') }}" class="btn btn-soft">Back</a><a href="{{ route('patients.edit', $patient) }}" class="btn btn-navy"><i class="bi bi-pencil"></i> Edit</a></div>
</div>
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card-soft p-4">
            <h5>Details</h5>
            <dl class="row mb-0 small">
                <dt class="col-5">Birthdate</dt><dd class="col-7">{{ $patient->birthdate->format('M d, Y') }} ({{ $patient->birthdate->age }} yrs)</dd>
                <dt class="col-5">Sex</dt><dd class="col-7">{{ ucfirst($patient->sex) }}</dd>
                <dt class="col-5">Contact</dt><dd class="col-7">{{ $patient->contact_number ?: '—' }}</dd>
                <dt class="col-5">Address</dt><dd class="col-7">{{ $patient->address ?: '—' }}</dd>
                <dt class="col-5">Emergency</dt><dd class="col-7">{{ $patient->emergency_contact_name ?: '—' }} {{ $patient->emergency_contact_number }}</dd>
                <dt class="col-5">Blood Type</dt><dd class="col-7">{{ $patient->blood_type ?: '—' }}</dd>
                <dt class="col-5">Access</dt><dd class="col-7">{{ $patient->dialysis_access_type ?: '—' }}</dd>
                <dt class="col-5">Dry Weight</dt><dd class="col-7">{{ $patient->dry_weight_kg ? $patient->dry_weight_kg . ' kg' : '—' }}</dd>
                <dt class="col-5">Allergies</dt><dd class="col-7">{{ $patient->allergies ?: '—' }}</dd>
            </dl>
        </div>
    </div>
    <div class="col-lg-7 d-flex flex-column gap-3">
        <div class="card-soft p-4">
            <h5>Recent Appointments</h5>
            @forelse ($patient->appointments as $a)
                <a href="{{ route('appointments.show', $a) }}" class="list-row">
                    <div class="flex-grow-1"><strong>{{ $a->appointment_date->format('M d, Y') }}</strong> <small class="text-muted">{{ \Carbon\Carbon::parse($a->appointment_time)->format('g:i A') }}</small></div>
                    @include('partials.pill', ['status' => $a->status])
                </a>
            @empty <p class="text-muted mb-0">No appointments yet.</p> @endforelse
        </div>
        <div class="card-soft p-4">
            <h5>Recent Charges</h5>
            @forelse ($patient->charges as $c)
                <div class="list-row"><div class="flex-grow-1">{{ $c->description }} <small class="text-muted">{{ $c->charge_date->format('M d') }}</small></div>
                    <strong>₱{{ number_format($c->amount, 2) }}</strong> @include('partials.pill', ['status' => $c->status])</div>
            @empty <p class="text-muted mb-0">No charges yet.</p> @endforelse
        </div>
    </div>
</div>
@endsection
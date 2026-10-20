@extends('demo.layout')

@section('title', $patient['first_name'] . ' ' . $patient['last_name'])

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $patient['first_name'] }} {{ $patient['last_name'] }}</h1>
        <p>{{ $patient['patient_code'] }} · @include('partials.pill', ['status' => $patient['status']])</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('demo.patients') }}" class="btn btn-soft">Back</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card-soft p-4">
            <h5>Details</h5>
            <dl class="row mb-0 small">
                <dt class="col-5">Birthdate</dt><dd class="col-7">{{ \Carbon\Carbon::parse($patient['birthdate'])->format('M d, Y') }} ({{ \Carbon\Carbon::parse($patient['birthdate'])->age }} yrs)</dd>
                <dt class="col-5">Sex</dt><dd class="col-7">{{ ucfirst($patient['sex']) }}</dd>
                <dt class="col-5">Contact</dt><dd class="col-7">{{ $patient['contact_number'] }}</dd>
                <dt class="col-5">Address</dt><dd class="col-7">{{ $patient['address'] }}</dd>
                <dt class="col-5">Emergency</dt><dd class="col-7">{{ $patient['emergency_contact_name'] }} · {{ $patient['emergency_contact_number'] }}</dd>
                <dt class="col-5">Blood Type</dt><dd class="col-7">{{ $patient['blood_type'] }}</dd>
                <dt class="col-5">Access</dt><dd class="col-7">{{ $patient['dialysis_access_type'] }}</dd>
                <dt class="col-5">Dry Weight</dt><dd class="col-7">{{ $patient['dry_weight_kg'] }} kg</dd>
                <dt class="col-5">Allergies</dt><dd class="col-7">{{ $patient['allergies'] }}</dd>
            </dl>
        </div>
    </div>
    <div class="col-lg-7 d-flex flex-column gap-3">
        <div class="card-soft p-4">
            <h5>Recent Appointments</h5>
            @foreach ($appointments as $appointment)
                <div class="list-row">
                    <div class="flex-grow-1"><strong>{{ \Carbon\Carbon::parse($appointment['appointment_date'])->format('M d, Y') }}</strong> <small class="text-muted">{{ \Carbon\Carbon::parse($appointment['appointment_time'])->format('g:i A') }}</small></div>
                    @include('partials.pill', ['status' => $appointment['status']])
                </div>
            @endforeach
        </div>
        <div class="card-soft p-4">
            <h5>Recent Charges</h5>
            @foreach ($charges as $charge)
                <div class="list-row">
                    <div class="flex-grow-1">{{ $charge['description'] }} <small class="text-muted">{{ \Carbon\Carbon::parse($charge['charge_date'])->format('M d') }}</small></div>
                    <strong>₱{{ number_format($charge['amount'], 2) }}</strong>
                    @include('partials.pill', ['status' => $charge['status']])
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@extends('demo.layout')

@section('title', 'Demo Home')

@section('content')
<div class="page-head">
    <div>
        <h1>Demo Mode</h1>
        <p>Fictional clinic data for public browsing only.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    @include('partials.stat', ['icon' => 'bi-people', 'label' => 'Active Patients', 'value' => $stats['totalPatients']])
    @include('partials.stat', ['icon' => 'bi-calendar2-check', 'label' => 'Appointments Today', 'value' => $stats['todaysAppointments']])
    @include('partials.stat', ['icon' => 'bi-receipt', 'label' => 'Unpaid Charges', 'value' => '₱' . number_format($stats['unpaidChargesTotal'], 2), 'sub' => $stats['unpaidChargesCount'] . ' open'])
    @include('partials.stat', ['icon' => 'bi-bell', 'label' => 'Announcements', 'value' => $stats['notificationCount']])
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-soft p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Today's Appointments</h5>
                <a href="{{ route('demo.appointments') }}" class="small">View all</a>
            </div>
            @foreach ($appointments as $appointment)
                @php $patient = collect($patients)->firstWhere('id', $appointment['patient_id']); @endphp
                <a href="{{ route('demo.patient', $appointment['patient_id']) }}" class="list-row">
                    <div class="avatar"><i class="bi bi-person"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $patient['first_name'] }} {{ $patient['last_name'] }}</div>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($appointment['appointment_time'])->format('g:i A') }}</small>
                    </div>
                    @include('partials.pill', ['status' => $appointment['status']])
                </a>
            @endforeach
        </div>
    </div>
    <div class="col-lg-5 d-flex flex-column gap-3">
        <div class="card-soft p-4">
            <h5>Quick Links</h5>
            <div class="d-grid gap-2">
                <a href="{{ route('demo.patients') }}" class="btn btn-navy"><i class="bi bi-person-lines-fill"></i> Demo Patients</a>
                <a href="{{ route('demo.appointments') }}" class="btn btn-soft"><i class="bi bi-calendar-plus"></i> Demo Appointments</a>
                <a href="{{ route('demo.treatment-records') }}" class="btn btn-soft"><i class="bi bi-clipboard2-plus"></i> Demo Treatments</a>
                <a href="{{ route('demo.charges') }}" class="btn btn-soft"><i class="bi bi-receipt"></i> Demo Billing</a>
            </div>
        </div>
        <div class="card-soft p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Announcements</h5>
                <a href="{{ route('demo.notifications') }}" class="small">View all</a>
            </div>
            @foreach ($notifications as $notification)
                <div class="mb-2">
                    <div class="fw-semibold small">{{ $notification['title'] }}</div>
                    <small class="text-muted">{{ Str::limit($notification['message'], 80) }}</small>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

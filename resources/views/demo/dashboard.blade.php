@extends('demo.layout')

@section('title', 'Demo Dashboard')

@section('content')
<div class="page-head">
    <div>
        <h1>Demo Dashboard</h1>
        <p>Read-only preview of operations and staffing activity.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    @include('partials.stat', ['icon' => 'bi-people', 'label' => 'Active Patients', 'value' => $stats['totalPatients']])
    @include('partials.stat', ['icon' => 'bi-calendar2-check', 'label' => 'Appointments Today', 'value' => $stats['todaysAppointments']])
    @include('partials.stat', ['icon' => 'bi-receipt', 'label' => 'Unpaid Charges', 'value' => '₱' . number_format($stats['unpaidChargesTotal'], 2), 'sub' => $stats['unpaidChargesCount'] . ' open'])
    @include('partials.stat', ['icon' => 'bi-bell', 'label' => 'Notifications', 'value' => $stats['notificationCount']])
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-soft p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2"><h5 class="mb-0">Today’s Schedule</h5></div>
            @foreach ($appointments as $appointment)
                @php $patient = collect($patients)->firstWhere('id', $appointment['patient_id']); @endphp
                <div class="list-row">
                    <div class="flex-grow-1"><div class="fw-semibold">{{ $patient['first_name'] }} {{ $patient['last_name'] }}</div><small class="text-muted">{{ \Carbon\Carbon::parse($appointment['appointment_time'])->format('g:i A') }}</small></div>
                    @include('partials.pill', ['status' => $appointment['status']])
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-soft p-4">
            <h5>Announcements</h5>
            @foreach ($notifications as $notification)
                <div class="mb-3">
                    <div class="fw-semibold small">{{ $notification['title'] }}</div>
                    <small class="text-muted">{{ $notification['message'] }}</small>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

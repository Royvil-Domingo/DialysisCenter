{{-- Owner: Royvil | Data: $totalPatients, $todaysAppointments, $unpaidChargesTotal, $unpaidChargesCount, $notifications --}}
@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="page-head"><div><h1>Welcome back, {{ auth()->user()->name }}</h1><p>Here's today's overview.</p></div></div>

<div class="row g-3 mb-4">
    @include('partials.stat', ['icon' => 'bi-people', 'label' => 'Active Patients', 'value' => $totalPatients])
    @include('partials.stat', ['icon' => 'bi-calendar2-check', 'label' => 'Appointments Today', 'value' => $todaysAppointments->count()])
    @include('partials.stat', ['icon' => 'bi-receipt', 'label' => 'Unpaid Charges', 'value' => '₱' . number_format($unpaidChargesTotal, 2), 'sub' => $unpaidChargesCount . ' open'])
    @include('partials.stat', ['icon' => 'bi-bell', 'label' => 'Notifications', 'value' => $notifications->count()])
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-soft p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2"><h5 class="mb-0">Today's Appointments</h5><a href="{{ route('appointments.index') }}" class="small">View all</a></div>
            @forelse ($todaysAppointments as $a)
                <a href="{{ route('appointments.show', $a) }}" class="list-row">
                    <div class="avatar"><i class="bi bi-person"></i></div>
                    <div class="flex-grow-1"><div class="fw-semibold">{{ $a->patient->full_name }}</div>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($a->appointment_time)->format('g:i A') }}</small></div>
                    @include('partials.pill', ['status' => $a->status])
                </a>
            @empty
                <p class="text-muted mb-0">No appointments scheduled for today.</p>
            @endforelse
        </div>
    </div>
    <div class="col-lg-5 d-flex flex-column gap-3">
        <div class="card-soft p-4">
            <h5>Quick Actions</h5>
            <div class="d-grid gap-2">
                <a href="{{ route('patients.create') }}" class="btn btn-navy"><i class="bi bi-person-plus"></i> Register Patient</a>
                <a href="{{ route('appointments.create') }}" class="btn btn-soft"><i class="bi bi-calendar-plus"></i> Schedule Appointment</a>
                <a href="{{ route('treatment-records.create') }}" class="btn btn-soft"><i class="bi bi-clipboard2-plus"></i> Record Treatment</a>
                <a href="{{ route('charges.create') }}" class="btn btn-soft"><i class="bi bi-receipt"></i> Record Charge</a>
            </div>
        </div>
        <div class="card-soft p-4">
            <div class="d-flex justify-content-between align-items-center mb-2"><h5 class="mb-0">Notifications</h5><a href="{{ route('notifications.index') }}" class="small">View all</a></div>
            @forelse ($notifications as $n)
                <div class="mb-2"><div class="fw-semibold small">{{ $n->title }}</div><small class="text-muted">{{ \Illuminate\Support\Str::limit($n->message, 80) }}</small></div>
            @empty
                <p class="text-muted mb-0 small">No notifications.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
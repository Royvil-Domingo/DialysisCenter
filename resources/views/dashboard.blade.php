{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1>Dashboard</h1>
    <p>Active patients: {{ $totalPatients }}</p>
    <p>Today's appointments: {{ $todaysAppointments->count() }}</p>
    <p>Unpaid charges: ₱{{ number_format($unpaidChargesTotal, 2) }} ({{ $unpaidChargesCount }} charges)</p>

    <h2>Notifications</h2>
    <ul>
        @forelse ($notifications as $n)
            <li><strong>{{ $n->title }}</strong> — {{ $n->message }}</li>
        @empty
            <li>No notifications.</li>
        @endforelse
    </ul>
@endsection
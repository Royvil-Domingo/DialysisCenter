{{-- Owner: Royvil — public appointment lookup (patient_code + birthdate) --}}
@extends('layouts.guest')
@section('title', 'Check My Appointment')
@section('content')
<div class="auth-wrap">
    <div class="card-soft p-4 p-md-5" style="width:min(520px,100%)">
        <a href="{{ url('/') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Back</a>
        <h3 class="mt-3">Check My Appointment</h3>
        <p class="text-muted">Enter the reference code given by our staff and your birthdate.</p>
        <form method="POST" action="{{ route('lookup.search') }}" class="row g-3">
            @csrf
            @include('partials.field', ['name' => 'patient_code', 'label' => 'Reference Code', 'col' => 'col-12', 'required' => true])
            @include('partials.field', ['name' => 'birthdate', 'label' => 'Birthdate', 'type' => 'date', 'col' => 'col-12', 'required' => true])
            <div class="col-12"><button type="submit" class="btn btn-navy w-100">Check</button></div>
        </form>

        @isset($patientName)
            <hr>
            <h5>Hello, {{ $patientName }}</h5>
            @forelse ($appointments as $a)
                <div class="list-row border-bottom">
                    <div class="flex-grow-1"><strong>{{ $a->appointment_date->format('D, M d, Y') }}</strong><br>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($a->appointment_time)->format('g:i A') }}</small></div>
                    @include('partials.pill', ['status' => $a->status])
                </div>
            @empty
                <p class="text-muted mb-0">No upcoming appointments found.</p>
            @endforelse
        @endisset
    </div>
</div>
@endsection
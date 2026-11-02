@extends('demo.layout')

@section('title', 'Demo Appointments')

@section('content')
<div class="page-head">
    <div>
        <h1>Demo Appointments</h1>
        <p>Sample schedule overview for public navigation.</p>
    </div>
    <a href="{{ route('demo.appointments.create') }}" class="btn btn-navy disabled" aria-disabled="true" tabindex="-1">Create (Demo only)</a>
</div>

<div class="card-soft p-3 p-md-4">
    <div class="table-responsive">
        <table class="table table-soft align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($appointments as $appointment)
                    @php $patient = collect($patients)->firstWhere('id', $appointment['patient_id']); @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($appointment['appointment_date'])->format('M d, Y') }}</td>
                        <td class="fw-semibold">{{ $patient['first_name'] }} {{ $patient['last_name'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($appointment['appointment_time'])->format('g:i A') }}</td>
                        <td>@include('partials.pill', ['status' => $appointment['status']])</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

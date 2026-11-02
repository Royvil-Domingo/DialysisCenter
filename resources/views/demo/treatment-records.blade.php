@extends('demo.layout')

@section('title', 'Demo Treatments')

@section('content')
<div class="page-head">
    <div>
        <h1>Demo Treatment Records</h1>
        <p>Representative dialysis session progress for display only.</p>
    </div>
</div>

<div class="card-soft p-3 p-md-4">
    <div class="table-responsive">
        <table class="table table-soft align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    @php $patient = collect($patients)->firstWhere('id', $record['patient_id']); @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($record['session_date'])->format('M d, Y') }}</td>
                        <td class="fw-semibold">{{ $patient['first_name'] }} {{ $patient['last_name'] }}</td>
                        <td>@include('partials.pill', ['status' => $record['status']])</td>
                        <td>{{ $record['notes'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

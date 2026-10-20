@extends('demo.layout')

@section('title', 'Demo Patients')

@section('content')
<div class="page-head">
    <div>
        <h1>Demo Patients</h1>
        <p>Sample patient records for presentation and navigation testing.</p>
    </div>
</div>

<div class="card-soft p-3 p-md-4">
    <div class="table-responsive">
        <table class="table table-soft align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Sex</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th class="text-end">View</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($patients as $patient)
                    <tr>
                        <td>{{ $patient['patient_code'] }}</td>
                        <td class="fw-semibold">{{ $patient['last_name'] }}, {{ $patient['first_name'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($patient['birthdate'])->age }}</td>
                        <td>{{ ucfirst($patient['sex']) }}</td>
                        <td>{{ $patient['contact_number'] }}</td>
                        <td>@include('partials.pill', ['status' => $patient['status']])</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('demo.patient', $patient['id']) }}" class="icon-btn" title="View"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

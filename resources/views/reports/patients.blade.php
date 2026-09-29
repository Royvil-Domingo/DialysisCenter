{{-- Owner: Cheyenne | Data: $patients (paginated, appointments_count, treatment_records_count) --}}
@extends('layouts.app')
@section('title', 'Patient Report')
@section('content')
<div class="page-head">
    <div><h1>Patient Report</h1><p>{{ now()->format('F d, Y') }}</p></div>
    <div class="d-flex gap-2 no-print"><a href="{{ route('reports.index') }}" class="btn btn-soft">Back</a><button type="button" class="btn btn-navy" onclick="window.print()"><i class="bi bi-printer"></i> Print</button></div>
</div>
<div class="card-soft p-3 p-md-4">
    <form method="GET" class="row g-2 mb-3 no-print">
        <div class="col-md-4"><select name="status" class="form-select"><option value="">All statuses</option>
            @foreach (['active', 'inactive'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
        <div class="col-md-4"><button class="btn btn-navy">Filter</button></div>
    </form>
    <div class="table-responsive">
        <table class="table table-soft align-middle mb-0">
            <thead><tr><th>Code</th><th>Name</th><th>Age</th><th>Sex</th><th class="text-center">Appointments</th><th class="text-center">Sessions</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($patients as $p)
                <tr><td>{{ $p->patient_code }}</td><td class="fw-semibold">{{ $p->last_name }}, {{ $p->first_name }}</td><td>{{ $p->birthdate->age }}</td><td>{{ ucfirst($p->sex) }}</td>
                    <td class="text-center">{{ $p->appointments_count }}</td><td class="text-center">{{ $p->treatment_records_count }}</td><td>@include('partials.pill', ['status' => $p->status])</td></tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $patients])
</div>
@endsection
{{-- Owner: Rian | Data: $patients (paginated) --}}
@extends('layouts.app')
@section('title', 'Patients')
@section('content')
<div class="page-head">
    <div><h1>Patients</h1><p>Registered dialysis patients</p></div>
    <a href="{{ route('patients.create') }}" class="btn btn-navy"><i class="bi bi-plus-lg"></i> Add Patient</a>
</div>
<div class="card-soft p-3 p-md-4">
    <form method="GET" class="row g-2 mb-3 no-print">
        <div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name or patient code"></div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach (['active', 'inactive'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-navy">Filter</button><a href="{{ route('patients.index') }}" class="btn btn-soft">Reset</a></div>
    </form>
    <div class="table-responsive">
        <table class="table table-soft align-middle mb-0">
            <thead><tr><th>Code</th><th>Name</th><th>Age</th><th>Sex</th><th>Contact</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse ($patients as $p)
                <tr>
                    <td>{{ $p->patient_code }}</td>
                    <td class="fw-semibold">{{ $p->last_name }}, {{ $p->first_name }}</td>
                    <td>{{ $p->birthdate->age }}</td>
                    <td>{{ ucfirst($p->sex) }}</td>
                    <td>{{ $p->contact_number ?: '—' }}</td>
                    <td>@include('partials.pill', ['status' => $p->status])</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('patients.show', $p) }}" class="icon-btn" title="View"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('patients.edit', $p) }}" class="icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                        @if ($p->status === 'active')
                            <form method="POST" action="{{ route('patients.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Mark this patient as inactive?')">
                                @csrf @method('DELETE')<button class="icon-btn danger" title="Deactivate"><i class="bi bi-person-x"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No patients found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $patients])
</div>
@endsection
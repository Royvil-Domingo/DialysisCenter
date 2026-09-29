{{-- Owner: Cheyenne --}}
@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="page-head"><div><h1>Reports</h1><p>Summaries you can print or save as PDF</p></div></div>
<div class="row g-3">
    <div class="col-md-6"><a href="{{ route('reports.patients') }}" class="card-soft p-4 d-block text-decoration-none h-100"><i class="bi bi-people fs-1"></i><h5 class="mt-2">Patient Report</h5><p class="text-muted mb-0">Patients with their appointment and session counts.</p></a></div>
    <div class="col-md-6"><a href="{{ route('reports.appointments') }}" class="card-soft p-4 d-block text-decoration-none h-100"><i class="bi bi-calendar2-week fs-1"></i><h5 class="mt-2">Appointment Report</h5><p class="text-muted mb-0">Appointments by date range and status.</p></a></div>
</div>
@endsection
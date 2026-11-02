@extends('demo.layout')

@section('title', 'Demo Reports')

@section('content')
<div class="page-head"><div><h1>Demo Reports</h1><p>Sample summaries for public presentation.</p></div></div>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card-soft p-4 h-100">
            <i class="bi bi-people fs-1"></i>
            <h5 class="mt-2">Patient Report</h5>
            <p class="text-muted mb-0">{{ count($patients) }} patients included in this fictional overview.</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-soft p-4 h-100">
            <i class="bi bi-calendar2-week fs-1"></i>
            <h5 class="mt-2">Appointment Report</h5>
            <p class="text-muted mb-0">{{ count($appointments) }} sample appointments used for presentation.</p>
        </div>
    </div>
</div>
@endsection

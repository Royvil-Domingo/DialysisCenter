{{-- Owner: Cheyenne | Data: $appointments, $summary (count by status), $from, $to --}}
@extends('layouts.app')
@section('title', 'Appointment Report')
@section('content')
<div class="page-head">
    <div><h1>Appointment Report</h1><p>{{ \Carbon\Carbon::parse($from)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</p></div>
    <div class="d-flex gap-2 no-print"><a href="{{ route('reports.index') }}" class="btn btn-soft">Back</a><button type="button" class="btn btn-navy" onclick="window.print()"><i class="bi bi-printer"></i> Print</button></div>
</div>
<form method="GET" class="card-soft p-3 mb-3 row g-2 no-print">
    <div class="col-md-4"><label class="small fw-semibold">From</label><input type="date" name="from" value="{{ $from }}" class="form-control"></div>
    <div class="col-md-4"><label class="small fw-semibold">To</label><input type="date" name="to" value="{{ $to }}" class="form-control"></div>
    <div class="col-md-4 d-flex align-items-end"><button class="btn btn-navy">Apply</button></div>
</form>
<div class="row g-3 mb-3">
    @include('partials.stat', ['icon' => 'bi-calendar2', 'label' => 'Scheduled', 'value' => $summary->get('scheduled', 0)])
    @include('partials.stat', ['icon' => 'bi-check2-circle', 'label' => 'Completed', 'value' => $summary->get('completed', 0)])
    @include('partials.stat', ['icon' => 'bi-x-circle', 'label' => 'Cancelled', 'value' => $summary->get('cancelled', 0)])
    @include('partials.stat', ['icon' => 'bi-person-dash', 'label' => 'No show', 'value' => $summary->get('no_show', 0)])
</div>
<div class="card-soft p-3 p-md-4">
    <div class="table-responsive">
        <table class="table table-soft align-middle mb-0">
            <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($appointments as $a)
                <tr><td>{{ $a->appointment_date->format('M d, Y') }}</td><td>{{ \Carbon\Carbon::parse($a->appointment_time)->format('g:i A') }}</td>
                    <td>{{ $a->patient->full_name }}</td><td>@include('partials.pill', ['status' => $a->status])</td></tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No appointments in this range.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
{{-- Owner: Cheyenne | Data: $record (patient, appointment, charge) --}}
@extends('layouts.app')
@section('title', 'Treatment Record')
@section('content')
<div class="page-head">
    <div><h1>Treatment Session</h1><p>{{ $record->session_date->format('l, M d, Y') }} · @include('partials.pill', ['status' => $record->status])</p></div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('treatment-records.index') }}" class="btn btn-soft">Back</a>
        <a href="{{ route('treatment-records.edit', $record) }}" class="btn btn-navy"><i class="bi bi-pencil"></i> Edit</a>
        <form method="POST" action="{{ route('treatment-records.destroy', $record) }}" onsubmit="return confirm('Delete this treatment record?')">@csrf @method('DELETE')<button class="btn btn-soft text-danger"><i class="bi bi-trash"></i> Delete</button></form>
    </div>
</div>
<div class="card-soft p-4" style="max-width:720px">
    <dl class="row mb-0">
        <dt class="col-sm-4">Patient</dt><dd class="col-sm-8"><a href="{{ route('patients.show', $record->patient) }}">{{ $record->patient->full_name }}</a></dd>
        <dt class="col-sm-4">Weight</dt><dd class="col-sm-8">{{ $record->pre_weight_kg ?? '—' }} → {{ $record->post_weight_kg ?? '—' }} kg</dd>
        <dt class="col-sm-4">Blood Pressure</dt><dd class="col-sm-8">{{ $record->blood_pressure_pre ?: '—' }} → {{ $record->blood_pressure_post ?: '—' }}</dd>
        <dt class="col-sm-4">Duration</dt><dd class="col-sm-8">{{ $record->duration_minutes ? $record->duration_minutes . ' minutes' : '—' }}</dd>
        <dt class="col-sm-4">Dialyzer</dt><dd class="col-sm-8">{{ $record->dialyzer_used ?: '—' }}</dd>
        <dt class="col-sm-4">Complications</dt><dd class="col-sm-8">{{ $record->complications ?: 'None recorded' }}</dd>
        <dt class="col-sm-4">Linked Charge</dt>
        <dd class="col-sm-8">
            @if ($record->charge) ₱{{ number_format($record->charge->amount, 2) }} @include('partials.pill', ['status' => $record->charge->status])
            @else <a href="{{ route('charges.create') }}">Record a charge</a> @endif
        </dd>
    </dl>
</div>
@endsection
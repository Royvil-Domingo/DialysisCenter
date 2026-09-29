{{-- Owner: Dren | Data: $appointment, $patients, $staff --}}
@extends('layouts.app')
@section('title', 'Edit Appointment')
@section('content')
<div class="page-head"><div><h1>Edit Appointment</h1><p>#{{ $appointment->id }} · {{ $appointment->patient->full_name }}</p></div></div>
<form method="POST" action="{{ route('appointments.update', $appointment) }}" class="card-soft p-4">
    @csrf @method('PUT')
    @include('appointments.partials.form')
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('appointments.show', $appointment) }}" class="btn btn-soft">Cancel</a><button type="submit" class="btn btn-navy">Save Changes</button></div>
</form>
@endsection
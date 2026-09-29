{{-- Owner: Dren | Data: $patients, $staff --}}
@extends('layouts.app')
@section('title', 'Schedule Appointment')
@section('content')
<div class="page-head"><div><h1>Schedule Appointment</h1><p>Appointments › New</p></div></div>
<form method="POST" action="{{ route('appointments.store') }}" class="card-soft p-4">
    @csrf
    @include('appointments.partials.form')
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('appointments.index') }}" class="btn btn-soft">Cancel</a><button type="submit" class="btn btn-navy">Save Appointment</button></div>
</form>
@endsection
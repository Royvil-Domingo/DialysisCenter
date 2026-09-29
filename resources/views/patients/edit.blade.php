{{-- Owner: Rian | Data: $patient --}}
@extends('layouts.app')
@section('title', 'Edit Patient')
@section('content')
<div class="page-head"><div><h1>Edit Patient</h1><p>{{ $patient->full_name }} · {{ $patient->patient_code }}</p></div></div>
<form method="POST" action="{{ route('patients.update', $patient) }}" class="card-soft p-4">
    @csrf @method('PUT')
    @include('patients.partials.form')
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('patients.show', $patient) }}" class="btn btn-soft">Cancel</a><button type="submit" class="btn btn-navy">Save Changes</button></div>
</form>
@endsection
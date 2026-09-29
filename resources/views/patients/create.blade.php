{{-- Owner: Rian --}}
@extends('layouts.app')
@section('title', 'Register Patient')
@section('content')
<div class="page-head"><div><h1>Register Patient</h1><p>Patients › Add new</p></div></div>
<form method="POST" action="{{ route('patients.store') }}" class="card-soft p-4">
    @csrf
    @include('patients.partials.form')
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('patients.index') }}" class="btn btn-soft">Cancel</a><button type="submit" class="btn btn-navy">Save Patient</button></div>
</form>
@endsection
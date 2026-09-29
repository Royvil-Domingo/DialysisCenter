{{-- Owner: Vince | Data: $charge, $patients --}}
@extends('layouts.app')
@section('title', 'Edit Charge')
@section('content')
<div class="page-head"><div><h1>Edit Charge</h1><p>#{{ $charge->id }} · {{ $charge->patient->full_name }}</p></div></div>
<form method="POST" action="{{ route('charges.update', $charge) }}" class="card-soft p-4">
    @csrf @method('PUT')
    @include('charges.partials.form')
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('charges.index') }}" class="btn btn-soft">Cancel</a><button type="submit" class="btn btn-navy">Save Changes</button></div>
</form>
@endsection
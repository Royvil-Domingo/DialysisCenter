{{-- Owner: Vince | Data: $patients, $treatmentRecords --}}
@extends('layouts.app')
@section('title', 'Record Charge')
@section('content')
<div class="page-head"><div><h1>Record Charge</h1><p>Billing › New</p></div></div>
<form method="POST" action="{{ route('charges.store') }}" class="card-soft p-4">
    @csrf
    @include('charges.partials.form')
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('charges.index') }}" class="btn btn-soft">Cancel</a><button type="submit" class="btn btn-navy">Save Charge</button></div>
</form>
@endsection
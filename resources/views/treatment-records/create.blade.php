{{-- Owner: Cheyenne | Data: $patients, $appointment (nullable) --}}
@extends('layouts.app')
@section('title', 'Record Treatment Session')
@section('content')
<div class="page-head"><div><h1>Record Treatment Session</h1><p>Treatment records › New</p></div></div>
<form method="POST" action="{{ route('treatment-records.store') }}" class="card-soft p-4">
    @csrf
    @if ($appointment)<input type="hidden" name="appointment_id" value="{{ $appointment->id }}">@endif
    @include('treatment-records.partials.form')
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('treatment-records.index') }}" class="btn btn-soft">Cancel</a><button type="submit" class="btn btn-navy">Save Record</button></div>
</form>
@endsection
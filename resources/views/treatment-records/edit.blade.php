{{-- Owner: Cheyenne | Data: $record, $patients --}}
@extends('layouts.app')
@section('title', 'Edit Treatment Record')
@section('content')
<div class="page-head"><div><h1>Edit Treatment Record</h1><p>{{ $record->patient->full_name }} · {{ $record->session_date->format('M d, Y') }}</p></div></div>
<form method="POST" action="{{ route('treatment-records.update', $record) }}" class="card-soft p-4">
    @csrf @method('PUT')
    @include('treatment-records.partials.form')
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('treatment-records.show', $record) }}" class="btn btn-soft">Cancel</a><button type="submit" class="btn btn-navy">Save Changes</button></div>
</form>
@endsection+
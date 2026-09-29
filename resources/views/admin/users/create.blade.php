{{-- Owner: Dren --}}
@extends('layouts.app')
@section('title', 'Add Account')
@section('content')
<div class="page-head"><div><h1>Add Account</h1><p>Manage Users › New</p></div></div>
<form method="POST" action="{{ route('admin.users.store') }}" class="card-soft p-4">
    @csrf
    @include('admin.users.partials.form')
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('admin.users.index') }}" class="btn btn-soft">Cancel</a><button type="submit" class="btn btn-navy">Create Account</button></div>
</form>
@endsection
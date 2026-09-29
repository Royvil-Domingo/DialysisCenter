{{-- Owner: Dren | Data: $user --}}
@extends('layouts.app')
@section('title', 'Edit Account')
@section('content')
<div class="page-head"><div><h1>Edit Account</h1><p>{{ $user->name }}</p></div></div>
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="card-soft p-4">
    @csrf @method('PUT')
    @include('admin.users.partials.form')
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('admin.users.index') }}" class="btn btn-soft">Cancel</a><button type="submit" class="btn btn-navy">Save Changes</button></div>
</form>
@endsection
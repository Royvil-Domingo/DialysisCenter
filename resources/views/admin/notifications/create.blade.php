{{-- Owner: Vince --}}
@extends('layouts.app')
@section('title', 'Post Announcement')
@section('content')
<div class="page-head"><div><h1>Post Announcement</h1><p>Visible on staff dashboards</p></div></div>
<form method="POST" action="{{ route('admin.notifications.store') }}" class="card-soft p-4" style="max-width:820px">
    @csrf
    <div class="row g-3">
        @include('partials.field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'col' => 'col-12'])
        @include('partials.field', ['name' => 'audience', 'label' => 'Audience', 'type' => 'select', 'blank' => false, 'options' => ['all_staff' => 'All staff', 'admin_only' => 'Admins only']])
        @include('partials.field', ['name' => 'expires_at', 'label' => 'Expires (optional)', 'type' => 'datetime-local'])
        @include('partials.field', ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true, 'col' => 'col-12'])
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('notifications.index') }}" class="btn btn-soft">Cancel</a><button type="submit" class="btn btn-navy">Post</button></div>
</form>
@endsection
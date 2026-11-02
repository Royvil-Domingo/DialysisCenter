@extends('demo.layout')

@section('title', 'Demo Notifications')

@section('content')
<div class="page-head">
    <div>
        <h1>Demo Notifications</h1>
        <p>Public-facing announcements using fictional content.</p>
    </div>
</div>
<div class="d-flex flex-column gap-3">
    @foreach ($notifications as $notification)
        <div class="card-soft p-4">
            <div class="d-flex justify-content-between flex-wrap gap-1"><h5 class="mb-1">{{ $notification['title'] }}</h5><small class="text-muted">{{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}</small></div>
            <p class="mb-1">{{ $notification['message'] }}</p>
            <small class="text-muted">Audience: {{ $notification['audience'] }}</small>
        </div>
    @endforeach
</div>
@endsection

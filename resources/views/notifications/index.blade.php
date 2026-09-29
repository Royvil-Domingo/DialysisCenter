{{-- Owner: Vince | Data: $notifications (paginated) --}}
@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<div class="page-head">
    <div><h1>Notifications</h1><p>Announcements for staff</p></div>
    @if (auth()->user()->isAdmin())<a href="{{ route('admin.notifications.create') }}" class="btn btn-navy"><i class="bi bi-megaphone"></i> Post Announcement</a>@endif
</div>
<div class="d-flex flex-column gap-3">
    @forelse ($notifications as $n)
        <div class="card-soft p-4">
            <div class="d-flex justify-content-between flex-wrap gap-1"><h5 class="mb-1">{{ $n->title }}</h5><small class="text-muted">{{ $n->created_at->diffForHumans() }}</small></div>
            <p class="mb-1">{{ $n->message }}</p>
            <small class="text-muted">Posted by {{ $n->postedBy->name ?? 'System' }}@if ($n->audience === 'admin_only') · Admins only @endif</small>
        </div>
    @empty
        <div class="card-soft p-4 text-muted">No notifications right now.</div>
    @endforelse
</div>
@include('partials.pagination', ['paginator' => $notifications])
@endsection
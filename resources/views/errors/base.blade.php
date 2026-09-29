@extends('layouts.guest')
@section('title', $code)
@section('content')
<div class="auth-wrap">
    <div class="card-soft p-5 text-center" style="max-width:520px">
        <div class="display-1 fw-bold" style="color:var(--navy2)">{{ $code }}</div>
        <h4>{{ $heading }}</h4>
        <p class="text-muted">{{ $message }}</p>
        <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="btn btn-navy">Back to {{ auth()->check() ? 'Dashboard' : 'Home' }}</a>
    </div>
</div>
@endsection
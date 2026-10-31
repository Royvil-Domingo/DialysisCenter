{{-- Owner: Royvil — restyled laravel/ui login --}}
@extends('layouts.guest')
@section('title', 'Staff Login')
@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-art d-none d-md-flex">
            <div class="logo-circle mb-3" style="background:rgba(255,255,255,.25);color:#fff"><i class="bi bi-activity"></i></div>
            <h3>compassionate care.<br>every dialysis.<br>every patient.</h3>
        </div>
        <div class="auth-form">
            <a href="{{ url('/') }}" class="text-decoration-none text-dark"><i class="bi bi-arrow-left fs-5"></i></a><br>
            <span class="auth-title">Staff Login</span>
            <form method="POST" action="{{ route('login') }}" class="row g-3">
                @csrf
                @include('partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'col' => 'col-12', 'required' => true])
                @include('partials.field', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'col' => 'col-12', 'required' => true])
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}><label class="form-check-label small" for="remember">Remember me</label></div>
                    {{-- @if (Route::has('password.request'))<a class="small" href="{{ route('password.request') }}">Forgot Password?</a>@endif --}}
                </div>
                <div class="col-12"><button type="submit" class="btn btn-login">Login</button></div>
            </form>
            <p class="small text-muted mt-4 mb-0">Accounts are created by the center's administrator.</p>
        </div>
    </div>
</div>
@endsection
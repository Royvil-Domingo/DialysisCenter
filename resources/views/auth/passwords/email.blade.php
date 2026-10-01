@extends('layouts.guest')

@section('content')
<div class="auth-wrap">
    <div class="auth-card card-soft p-4 mx-auto" style="max-width:520px; align-items:center;">
        <div class="auth-form w-100">
            <a href="{{ url('/') }}" class="text-decoration-none text-dark"><i class="bi bi-arrow-left fs-5"></i></a>
            <div class="text-center mb-3">
                <h3 class="mb-1">Reset your password</h3>
                <p class="muted">Enter your account email to receive a reset link.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="row g-3">
                @csrf
                @include('partials.field', ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'col' => 'col-12', 'required' => true])

                <div class="col-12 d-flex justify-content-between align-items-center">
                    <a href="{{ route('login') }}" class="small">Back to login</a>
                    <button type="submit" class="btn btn-navy">Send Reset Link</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

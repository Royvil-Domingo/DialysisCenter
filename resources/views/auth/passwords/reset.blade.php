@extends('layouts.guest')

@section('content')
<div class="auth-wrap">
    <div class="auth-card card-soft p-4 mx-auto" style="max-width:520px; align-items:center;">
        <div class="auth-form w-100">
            <a href="{{ url('/') }}" class="text-decoration-none text-dark"><i class="bi bi-arrow-left fs-5"></i></a>
            <div class="text-center mb-3">
                <h3 class="mb-1">Set a new password</h3>
                <p class="muted">Choose a strong password for your account.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="row g-3">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                @include('partials.field', ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'col' => 'col-12', 'required' => true, 'value' => $email ?? old('email')])
                @include('partials.field', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'col' => 'col-12', 'required' => true])
                @include('partials.field', ['name' => 'password_confirmation', 'label' => 'Confirm Password', 'type' => 'password', 'col' => 'col-12', 'required' => true])

                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-navy">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

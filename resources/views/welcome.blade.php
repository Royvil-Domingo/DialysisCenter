{{-- Owner: Royvil — replaces the default Laravel page --}}
@extends('layouts.guest')
@section('title', 'Welcome')
@section('content')
@php
    $clinic = \App\Models\Setting::get('clinic_name', 'Dialysis Center');
    $addr = \App\Models\Setting::get('clinic_address', 'Poblacion, San Ildefonso, Bulacan');
    $hours = \App\Models\Setting::get('operating_hours', 'Mon–Sat, 6:00 AM – 6:00 PM');
    $phone = \App\Models\Setting::get('clinic_contact_number', '—');
    $email = \App\Models\Setting::get('clinic_email', '—');
@endphp
<nav class="container py-3 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2"><div class="avatar"><i class="bi bi-activity"></i></div><strong class="text-uppercase small">{{ $clinic }}</strong></div>
    <a href="{{ route('login') }}" class="btn btn-navy">Staff Login</a>
</nav>
<header class="container py-5">
    <div class="row align-items-center">
        <div class="col-lg-7">
            <h1 class="display-5">Compassionate care.<br>Every dialysis. Every patient.</h1>
            <p class="lead text-muted my-3">Check your dialysis schedule online or ask Diabot, our assistant, anything about your visit.</p>
            <a href="{{ route('lookup') }}" class="btn btn-navy btn-lg"><i class="bi bi-calendar2-check"></i> Check My Appointment</a>
        </div>
        <div class="col-lg-5 text-center d-none d-lg-block"><i class="bi bi-heart-pulse hero-icon"></i></div>
    </div>
</header>
<section class="container pb-5">
    <div class="row g-3">
        <div class="col-md-4"><div class="card-soft p-4 h-100"><i class="bi bi-clock fs-3"></i><h5 class="mt-2">Operating Hours</h5><p class="mb-0 text-muted">{{ $hours }}</p></div></div>
        <div class="col-md-4"><div class="card-soft p-4 h-100"><i class="bi bi-geo-alt fs-3"></i><h5 class="mt-2">Location</h5><p class="mb-0 text-muted">{{ $addr }}</p></div></div>
        <div class="col-md-4"><div class="card-soft p-4 h-100"><i class="bi bi-telephone fs-3"></i><h5 class="mt-2">Contact</h5><p class="mb-0 text-muted">{{ $phone }}<br>{{ $email }}</p></div></div>
    </div>
</section>
<footer class="site-foot text-center py-3 small">© {{ date('Y') }} {{ $clinic }}. All rights reserved.</footer>
@endsection
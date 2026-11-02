<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Demo') · {{ $clinic['name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app-theme.css') }}" rel="stylesheet">
</head>
<body>
<div class="d-lg-flex">
    <aside class="offcanvas-lg offcanvas-start app-sidebar d-flex flex-column p-3 no-print" tabindex="-1" id="demoSidebar">
        <div class="d-flex justify-content-end d-lg-none">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#demoSidebar"></button>
        </div>
        <div class="text-center mb-4">
            <div class="logo-circle"><i class="bi bi-activity"></i></div>
            <div class="small mt-2 fw-semibold">{{ $clinic['name'] }}</div>
        </div>
        <nav class="sidebar-nav nav flex-column gap-1">
            <a class="nav-link {{ request()->routeIs('demo.dashboard') ? 'active' : '' }}" href="{{ route('demo.dashboard') }}"> <i class="bi bi-house-door"></i>Dashboard </a>
            <a class="nav-link {{ request()->routeIs('demo.patients') || request()->routeIs('demo.patient') ? 'active' : '' }}" href="{{ route('demo.patients') }}"><i class="bi bi-people"></i>Patients</a>
            <a class="nav-link {{ request()->routeIs('demo.appointments') || request()->routeIs('demo.appointments.create') ? 'active' : '' }}" href="{{ route('demo.appointments') }}"><i class="bi bi-calendar2-check"></i>Appointments</a>
            <a class="nav-link {{ request()->routeIs('demo.treatment-records') ? 'active' : '' }}" href="{{ route('demo.treatment-records') }}"><i class="bi bi-clipboard2-pulse"></i>Treatment Records</a>
            <a class="nav-link {{ request()->routeIs('demo.charges') ? 'active' : '' }}" href="{{ route('demo.charges') }}"><i class="bi bi-receipt"></i>Billing</a>
            <a class="nav-link {{ request()->routeIs('demo.reports') ? 'active' : '' }}" href="{{ route('demo.reports') }}"><i class="bi bi-bar-chart"></i>Reports</a>
            <a class="nav-link {{ request()->routeIs('demo.notifications') ? 'active' : '' }}" href="{{ route('demo.notifications') }}"><i class="bi bi-bell"></i>Notifications</a>
            <div class="nav-label">Demo</div>
            <a class="nav-link" href="{{ route('welcome') }}"><i class="bi bi-box-arrow-in-left"></i>Real app</a>
        </nav>
        <div class="alert alert-warning mt-3 mb-0 small">Read-only demo mode. No real patient or clinic data is used.</div>
    </aside>

    <div class="flex-grow-1 min-w-0">
        <div class="topbar no-print">
            <button type="button" class="btn btn-soft d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#demoSidebar"><i class="bi bi-list"></i></button>
            <div class="text-muted small d-none d-sm-block">Demo preview · {{ now()->format('l, d F Y') }}</div>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="fs-4 text-dark" title="Read-only demo"><i class="bi bi-eye"></i></span>
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar">D</div>
                    <div class="lh-1 d-none d-sm-block">
                        <div class="fw-bold small">Demo Visitor</div>
                        <small class="text-muted text-capitalize">public mode</small>
                    </div>
                </div>
            </div>
        </div>

        <main class="p-3 p-md-4">
            @if (session('success'))
                <div class="alert alert-info">{{ session('success') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

{{-- Owner: Royvil — role-aware: admin-only links hidden for staff --}}
@php
    $u = auth()->user();
    $nav = [
        ['dashboard', 'bi-house-door', 'Dashboard', 'dashboard'],
        ['patients.index', 'bi-people', 'Patients', 'patients.*'],
        ['appointments.index', 'bi-calendar2-check', 'Appointments', 'appointments.*'],
        ['treatment-records.index', 'bi-clipboard2-pulse', 'Treatment Records', 'treatment-records.*'],
        ['charges.index', 'bi-receipt', 'Billing', 'charges.*'],
        ['reports.index', 'bi-bar-chart', 'Reports', 'reports.*'],
        ['notifications.index', 'bi-bell', 'Notifications', 'notifications.*'],
    ];
    $admin = [
        ['admin.users.index', 'bi-person-gear', 'Manage Users', 'admin.users.*'],
        ['admin.activity-logs.index', 'bi-journal-text', 'Activity Logs', 'admin.activity-logs.*'],
        ['admin.notifications.create', 'bi-megaphone', 'Post Announcement', 'admin.notifications.*'],
        ['admin.settings.edit', 'bi-gear', 'Settings', 'admin.settings.*'],
    ];
@endphp
<div class="offcanvas-lg offcanvas-start app-sidebar d-flex flex-column p-3 no-print" tabindex="-1" id="appSidebar">
    <div class="d-flex justify-content-end d-lg-none">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar"></button>
    </div>
    <div class="text-center mb-4">
        <div class="logo-circle"><i class="bi bi-activity"></i></div>
        <div class="small mt-2 fw-semibold">{{ \App\Models\Setting::get('clinic_name', 'Dialysis Center') }}</div>
    </div>
    <nav class="nav flex-column gap-1 flex-grow-1">
        @foreach ($nav as [$route, $icon, $label, $pattern])
            <a class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}">
                <i class="bi {{ $icon }}"></i>{{ $label }}
            </a>
        @endforeach
        @if ($u->isAdmin())
            <div class="nav-label">Administration</div>
            @foreach ($admin as [$route, $icon, $label, $pattern])
                <a class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}">
                    <i class="bi {{ $icon }}"></i>{{ $label }}
                </a>
            @endforeach
        @endif
    </nav>
    <button type="button" class="btn btn-logout mt-3" data-bs-toggle="modal" data-bs-target="#logoutModal">
        <i class="bi bi-box-arrow-right"></i> Log Out
    </button>
</div>
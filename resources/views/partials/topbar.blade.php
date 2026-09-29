{{-- Owner: Royvil --}}
<div class="topbar no-print">
    <button type="button" class="btn btn-soft d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#appSidebar"><i class="bi bi-list"></i></button>
    <div class="text-muted small d-none d-sm-block">{{ now()->format('l, d F Y') }}</div>
    <div class="ms-auto d-flex align-items-center gap-3">
        <button type="button" class="btn btn-link p-0 fs-4 text-dark" onclick="toggleChat()" title="Diabot"><i class="bi bi-robot"></i></button>
        <a href="{{ route('notifications.index') }}" class="fs-4 text-dark" title="Notifications"><i class="bi bi-bell"></i></a>
        <div class="d-flex align-items-center gap-2">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="lh-1 d-none d-sm-block">
                <div class="fw-bold small">{{ auth()->user()->name }}</div>
                <small class="text-muted text-capitalize">{{ auth()->user()->role }}</small>
            </div>
        </div>
    </div>
</div>
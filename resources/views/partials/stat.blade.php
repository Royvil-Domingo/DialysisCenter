<div class="col-sm-6 col-xl-3">
    <div class="card-soft p-3 d-flex align-items-center gap-3 h-100">
        <div class="stat-icon"><i class="bi {{ $icon }}"></i></div>
        <div>
            <div class="text-muted small">{{ $label }}</div>
            <div class="stat-value">{{ $value }}</div>
            @isset($sub)<small class="text-muted">{{ $sub }}</small>@endisset
        </div>
    </div>
</div>
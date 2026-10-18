<button type="button"
        class="password-toggle"
        data-target="{{ $target ?? '' }}"
        aria-label="Show password"
        aria-pressed="false"
        title="Show password">
    <svg viewBox="0 0 24 24" aria-hidden="true">
        <path class="password-eye-path" d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" />
        <circle class="password-eye-dot" cx="12" cy="12" r="3" />
        <path class="password-eye-slash" d="M4 4L20 20" />
    </svg>
</button>

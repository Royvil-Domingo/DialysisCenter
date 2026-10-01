{{-- params: name, label, type(text), value, options[], blank(true), required(false), step, col(col-md-6) --}}
@php
    $type = $type ?? 'text';
    $val = $type === 'password' ? '' : old($name, $value ?? null);
@endphp
<div class="{{ $col ?? 'col-md-6' }}">
    <label class="form-label small fw-semibold" for="f_{{ $name }}">{{ $label }}@if($required ?? false) <span class="text-danger">*</span>@endif</label>
    @if ($type === 'select')
        <select id="f_{{ $name }}" name="{{ $name }}" class="form-select @error($name) is-invalid @enderror" {{ ($required ?? false) ? 'required' : '' }}>
            @if ($blank ?? true)<option value="">— Select —</option>@endif
            @foreach ($options as $k => $l)
                <option value="{{ $k }}" @selected((string) $val === (string) $k)>{{ $l }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="f_{{ $name }}" name="{{ $name }}" rows="3" class="form-control @error($name) is-invalid @enderror">{{ $val }}</textarea>
    @elseif ($type === 'password')
        <div class="position-relative">
            <input id="f_{{ $name }}" type="password" name="{{ $name }}" value="{{ $val }}"
                   class="form-control pe-5 @error($name) is-invalid @enderror"
                   @if(isset($step)) step="{{ $step }}" @endif {{ ($required ?? false) ? 'required' : '' }}>
            <button type="button"
                    class="btn btn-link position-absolute end-0 top-50 translate-middle-y me-2 p-0 text-muted password-toggle"
                    data-target="f_{{ $name }}"
                    aria-label="Show password">
                <i class="bi bi-eye-slash password-eye-icon"></i>
            </button>
        </div>
    @else
        <input id="f_{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $val }}"
               class="form-control @error($name) is-invalid @enderror"
               @if(isset($step)) step="{{ $step }}" @endif {{ ($required ?? false) ? 'required' : '' }}>
    @endif
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<style>
    .password-toggle {
        transition: transform 0.18s ease, opacity 0.18s ease;
        opacity: 0.8;
    }

    .password-toggle:hover {
        opacity: 1;
        transform: translateY(-50%) scale(1.08);
    }

    .password-eye-icon {
        transition: transform 0.2s ease, opacity 0.2s ease, filter 0.2s ease;
        display: inline-block;
    }

    .password-toggle.is-visible .password-eye-icon {
        transform: rotate(-12deg) scale(1.12);
        filter: drop-shadow(0 0 4px rgba(0, 0, 0, 0.18));
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.password-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = document.getElementById(button.dataset.target);
                if (!input) return;

                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';

                const icon = button.querySelector('.password-eye-icon');
                if (icon) {
                    icon.classList.toggle('bi-eye', isPassword);
                    icon.classList.toggle('bi-eye-slash', !isPassword);
                }

                button.classList.toggle('is-visible', isPassword);
                button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        });
    });
</script>
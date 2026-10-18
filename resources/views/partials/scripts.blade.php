<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    .password-field { position: relative; }
    .password-toggle {
        position: absolute;
        top: 50%;
        right: 0.75rem;
        transform: translateY(-50%);
        z-index: 3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #6c757d;
        padding: 0;
        cursor: pointer;
        transition: opacity 0.2s ease, transform 0.2s ease, color 0.2s ease;
    }
    .password-toggle:hover,
    .password-toggle:focus-visible {
        color: #495057;
        opacity: 1;
        outline: none;
    }
    .password-toggle:active {
        transform: translateY(-50%) scale(0.96);
    }
    .password-toggle svg {
        width: 1.15rem;
        height: 1.15rem;
        display: block;
        overflow: visible;
    }
    .password-eye-path,
    .password-eye-dot,
    .password-eye-slash {
        vector-effect: non-scaling-stroke;
        transition: opacity 0.2s ease, transform 0.2s ease, stroke-dashoffset 0.2s ease;
    }
    .password-eye-path,
    .password-eye-dot {
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .password-eye-dot {
        fill: currentColor;
        stroke: none;
    }
    .password-eye-slash {
        fill: none;
        stroke: currentColor;
        stroke-width: 2.1;
        stroke-linecap: round;
        stroke-dasharray: 26;
        stroke-dashoffset: 0;
        opacity: 1;
        transform-origin: center;
    }
    .password-toggle.is-visible .password-eye-slash {
        opacity: 0;
        transform: scaleX(0.4);
        stroke-dashoffset: 26;
    }
    @media (prefers-reduced-motion: reduce) {
        .password-toggle,
        .password-eye-path,
        .password-eye-dot,
        .password-eye-slash {
            transition: none;
        }
    }
</style>
<script>
(function () {
    function setToggleState(button, hidden) {
        button.classList.toggle('is-visible', !hidden);
        button.setAttribute('aria-label', hidden ? 'Show password' : 'Hide password');
        button.setAttribute('aria-pressed', hidden ? 'false' : 'true');
        button.title = hidden ? 'Show password' : 'Hide password';
    }

    function bindButton(button) {
        if (!button || button.dataset.bound === '1') return;

        const targetId = button.dataset.target;
        const input = targetId ? document.getElementById(targetId) : null;

        if (!input) {
            return;
        }

        button.dataset.bound = '1';

        function togglePassword() {
            const shouldShow = input.type === 'password';
            const start = input.selectionStart;
            const end = input.selectionEnd;

            input.type = shouldShow ? 'text' : 'password';

            if (typeof start === 'number' && typeof end === 'number') {
                requestAnimationFrame(() => {
                    input.focus();
                    input.setSelectionRange(start, end);
                });
            } else {
                input.focus();
            }

            setToggleState(button, input.type === 'password');
        }

        button.addEventListener('click', function (event) {
            event.preventDefault();
            togglePassword();
        });

        setToggleState(button, input.type === 'password');
    }

    function ensureAutoToggle(input) {
        if (!input || input.type !== 'password') return;

        const wrapper = input.closest('.password-field');
        if (wrapper && wrapper.querySelector('.password-toggle')) return;

        const fieldWrapper = wrapper || document.createElement('div');
        if (!wrapper) {
            const parent = input.parentNode;
            parent.insertBefore(fieldWrapper, input);
            fieldWrapper.className = 'password-field position-relative';
            fieldWrapper.appendChild(input);
        }

        const existing = fieldWrapper.querySelector('.password-toggle');
        if (existing) return;

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'password-toggle';
        button.setAttribute('aria-label', 'Show password');
        button.setAttribute('aria-pressed', 'false');
        button.title = 'Show password';
        button.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path class="password-eye-path" d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" /><circle class="password-eye-dot" cx="12" cy="12" r="3" /><path class="password-eye-slash" d="M4 4L20 20" /></svg>';

        if (!input.id) {
            input.id = 'password-' + Math.random().toString(36).slice(2, 9);
        }

        button.dataset.target = input.id;
        fieldWrapper.appendChild(button);
        bindButton(button);
    }

    function setupPasswordToggles() {
        document.querySelectorAll('.password-toggle').forEach(bindButton);
        document.querySelectorAll('input[type="password"]').forEach(ensureAutoToggle);
    }

    document.addEventListener('DOMContentLoaded', setupPasswordToggles);

    const observer = new MutationObserver(function (mutations) {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (!(node instanceof HTMLElement)) continue;
                if (node.matches && node.matches('input[type="password"]')) {
                    ensureAutoToggle(node);
                }
                if (node.querySelectorAll) {
                    node.querySelectorAll('input[type="password"]').forEach(ensureAutoToggle);
                    node.querySelectorAll('.password-toggle').forEach(bindButton);
                }
            }
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
})();
</script>

<script>
// prevent double-submit (slow connection / double click)
document.addEventListener('submit', e => {
  if (e.defaultPrevented) return;
  const b = e.target.querySelector('[type=submit], button:not([type])');
  if (b) setTimeout(() => { b.disabled = true; b.insertAdjacentHTML('beforeend', ' <span class="spinner-border spinner-border-sm"></span>'); }, 0);
});
window.addEventListener('pageshow', ev => { if (ev.persisted) location.reload(); });
</script>
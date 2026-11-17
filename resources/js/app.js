import './bootstrap';

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const pageLoader = document.getElementById('pageLoader');

function showPageLoader() {
    if (!pageLoader) return;

    pageLoader.hidden = false;
    pageLoader.classList.remove('is-hidden');
    document.body.setAttribute('aria-busy', 'true');
}

function hidePageLoader() {
    if (!pageLoader) return;

    pageLoader.classList.add('is-hidden');
    document.body.setAttribute('aria-busy', 'false');

    window.setTimeout(() => {
        pageLoader.hidden = true;
    }, prefersReducedMotion ? 0 : 260);
}

function configurePageLoader() {
    if (!pageLoader) return;

    showPageLoader();

    window.addEventListener('load', () => {
        window.setTimeout(hidePageLoader, prefersReducedMotion ? 0 : 180);
    });

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            hidePageLoader();
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.target === '_blank' || form.getAttribute('target') === '_blank') return;
        if (form.method && form.method.toLowerCase() === 'get') return;

        const action = form.action || window.location.href;
        const targetUrl = new URL(action, window.location.href);
        if (targetUrl.origin === window.location.origin && !form.dataset.noSkeleton) {
            showPageLoader();
        }
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a');
        if (!link) return;
        if (link.target === '_blank' || link.getAttribute('target') === '_blank') return;
        if (link.hasAttribute('download') || link.hash || link.dataset.noSkeleton) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('mailto:') || href.startsWith('tel:')) return;

        const targetUrl = new URL(href, window.location.href);
        if (targetUrl.origin === window.location.origin && !href.startsWith('#')) {
            showPageLoader();
        }
    });
}

function animateCounter(element) {
    const rawText = element.dataset.count || element.textContent.trim();
    if (!rawText || rawText === '—') {
        return;
    }

    const stripped = rawText.replace(/[^\d.,-]/g, '');
    const numericValue = Number.parseFloat(stripped.replace(/,/g, ''));

    if (!Number.isFinite(numericValue)) {
        return;
    }

    const currencyPrefix = rawText.match(/^[^\d-]*/)?.[0] ?? '';
    const suffix = rawText.match(/[^\d.,-]*$/)?.[0] ?? '';
    const decimals = rawText.includes('.') ? 2 : 0;
    const duration = 700;
    const start = performance.now();

    const formatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });

    function tick(timestamp) {
        const progress = Math.min((timestamp - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const value = numericValue * eased;
        element.textContent = `${currencyPrefix}${formatter.format(value)}${suffix}`;

        if (progress < 1) {
            window.requestAnimationFrame(tick);
        } else {
            element.textContent = rawText;
        }
    }

    if (!prefersReducedMotion) {
        element.dataset.count = rawText;
        window.requestAnimationFrame(tick);
    }
}

function setupPageMotion() {
    document.body.classList.add('js-ready');

    const revealTargets = document.querySelectorAll('.card-soft, .auth-card, .page-head, .table-soft tbody tr, .list-row, .chat-launcher, .btn-navy, .btn-soft, .btn-login, .nav-link, .form-control, .form-select');

    revealTargets.forEach((element, index) => {
        element.classList.add('reveal');
        if (!prefersReducedMotion) {
            element.style.transitionDelay = `${Math.min(index * 30, 160)}ms`;
        }
    });

    if (!prefersReducedMotion) {
        const observer = new IntersectionObserver((entries, currentObserver) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    currentObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll('.reveal').forEach((element) => observer.observe(element));
    } else {
        document.querySelectorAll('.reveal').forEach((element) => element.classList.add('is-visible'));
    }

    document.querySelectorAll('.stat-value').forEach((element) => animateCounter(element));
}

function debounce(callback, delay = 250) {
    let timeoutId;

    return (...args) => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => callback(...args), delay);
    };
}

function initLivePatientSearch() {
    const form = document.querySelector('[data-live-search="patients"]');
    if (!form) {
        return;
    }

    const input = form.querySelector('input[name="search"]');
    const statusSelect = form.querySelector('select[name="status"]');
    const tbody = document.querySelector('.table-soft tbody');
    if (!input || !tbody) {
        return;
    }

    const updatePatients = debounce(async () => {
        const params = new URLSearchParams(new FormData(form));
        const url = `${window.location.pathname}?${params.toString()}`;

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
            });

            if (!response.ok) {
                return;
            }

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const nextTableBody = doc.querySelector('.table-soft tbody');
            const paginationWrapper = doc.querySelector('.d-flex.flex-wrap.justify-content-between.align-items-center.gap-2.mt-3.no-print');

            if (nextTableBody) {
                tbody.innerHTML = nextTableBody.innerHTML;
            }

            const currentPagination = document.querySelector('.d-flex.flex-wrap.justify-content-between.align-items-center.gap-2.mt-3.no-print');
            if (paginationWrapper) {
                if (currentPagination) {
                    currentPagination.innerHTML = paginationWrapper.innerHTML;
                } else {
                    const container = document.querySelector('.table-soft')?.parentElement;
                    if (container) {
                        container.insertAdjacentHTML('beforeend', paginationWrapper.outerHTML);
                    }
                }
            } else if (currentPagination) {
                currentPagination.remove();
            }
        } catch (error) {
            console.warn('Live patient search failed:', error);
        }
    }, 260);

    input.addEventListener('input', updatePatients);
    if (statusSelect) {
        statusSelect.addEventListener('change', updatePatients);
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        updatePatients();
    });
}

function initLookupSuggestions() {
    const form = document.querySelector('[data-lookup-search]');
    if (!form) {
        return;
    }

    const patientCodeInput = form.querySelector('input[name="patient_code"]');
    const birthdateInput = form.querySelector('input[name="birthdate"]');
    if (!patientCodeInput || !birthdateInput) {
        return;
    }

    const suggestionBox = document.createElement('div');
    suggestionBox.className = 'list-group mt-2';
    suggestionBox.setAttribute('aria-live', 'polite');
    form.appendChild(suggestionBox);

    const prompt = () => {
        const keyword = patientCodeInput.value.trim();
        if (keyword.length < 2) {
            suggestionBox.innerHTML = '';
            return;
        }

        fetch(`/lookup/suggest?q=${encodeURIComponent(keyword)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then((response) => response.ok ? response.json() : { results: [] })
            .then((payload) => {
                const results = payload.results || [];
                suggestionBox.innerHTML = '';

                if (!results.length) {
                    return;
                }

                results.forEach((item) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'list-group-item list-group-item-action text-start';
                    button.textContent = `${item.label}`;
                    button.addEventListener('click', () => {
                        patientCodeInput.value = item.patient_code || patientCodeInput.value;
                        if (item.birthdate) {
                            birthdateInput.value = item.birthdate;
                        }
                        suggestionBox.innerHTML = '';
                    });
                    suggestionBox.appendChild(button);
                });
            })
            .catch(() => {
                suggestionBox.innerHTML = '';
            });
    };

    patientCodeInput.addEventListener('input', debounce(prompt, 180));
}

configurePageLoader();
document.addEventListener('DOMContentLoaded', () => {
    setupPageMotion();
    initLivePatientSearch();
    initLookupSuggestions();
});

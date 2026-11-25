const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

export function setButtonLoading(button, loadingText) {
    if (!button || button.dataset.loading === 'true') {
        return false;
    }

    button.dataset.loading = 'true';
    button.dataset.originalText = button.textContent;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    button.innerHTML = '<span class="button-spinner" aria-hidden="true"></span><span>' + loadingText + '</span>';
    return true;
}

export function clearButtonLoading(button, restoredText) {
    if (!button) {
        return;
    }

    delete button.dataset.loading;
    button.disabled = false;
    button.removeAttribute('aria-busy');
    button.textContent = restoredText ?? button.dataset.originalText ?? button.textContent;
}

export function showToast(message, tone = 'success', actionLabel = null, action = null) {
    let region = document.querySelector('[data-toast-region]');

    if (!region) {
        region = document.createElement('div');
        region.className = 'toast-region';
        region.dataset.toastRegion = '';
        region.setAttribute('aria-live', 'polite');
        region.setAttribute('aria-atomic', 'true');
        document.body.append(region);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast--${tone}`;
    toast.setAttribute('role', tone === 'error' ? 'alert' : 'status');
    toast.textContent = message;
    if (actionLabel && typeof action === 'function') {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'toast__action';
        button.textContent = actionLabel;
        button.addEventListener('click', () => {
            button.disabled = true;
            action();
        });
        toast.append(button);
    }
    region.append(toast);
    window.requestAnimationFrame(() => toast.classList.add('toast--visible'));

    window.setTimeout(() => {
        toast.classList.remove('toast--visible');
        window.setTimeout(() => toast.remove(), reduceMotion.matches ? 20 : 220);
    }, 3200);
}

document.querySelectorAll('[data-slot-dialog] form, .login-form, .sidebar__account form').forEach((form) => {
    form.addEventListener('submit', () => {
        if (form.classList.contains('login-form') || form.closest('.sidebar__account')) {
            return;
        }
        const submit = form.querySelector('[type="submit"]');
        if (submit) {
            setButtonLoading(submit, submit.closest('.login-form') ? 'MEMPROSES...' : 'MENYIMPAN...');
        }
    });
});

document.querySelectorAll('[data-count-up]').forEach((element) => {
    const value = Number(element.dataset.countUp);
    if (!Number.isFinite(value) || reduceMotion.matches) {
        return;
    }

    const prefix = element.dataset.countPrefix ?? '';
    const suffix = element.dataset.countSuffix ?? '';
    const duration = 650;
    const startedAt = performance.now();

    const step = (now) => {
        const progress = Math.min(1, (now - startedAt) / duration);
        const eased = 1 - Math.pow(1 - progress, 3);
        element.textContent = prefix + Math.round(value * eased).toLocaleString('id-ID') + suffix;
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };

    window.requestAnimationFrame(step);
});

document.querySelectorAll('.surface, .chart-card, .stat-card, .slot-card').forEach((element, index) => {
    if (!element.classList.contains('reveal')) {
        element.style.setProperty('--reveal-delay', `${Math.min(index * 45, 270)}ms`);
        element.classList.add('reveal');
    }
});

const loginForm = document.querySelector('.login-form');

const usernameInput = document.querySelector('#username');
const passwordInput = document.querySelector('#login-password');
const loginLed = document.querySelector('.vending-login-screen__led');
const keypadKeys = document.querySelectorAll('.vending-side-panel__keypad i');
let keypadIndex = 0;

function pulseLoginKeypad() {
    if (!keypadKeys.length) return;
    keypadKeys.forEach((key) => key.classList.remove('is-active'));
    keypadKeys[keypadIndex % keypadKeys.length].classList.add('is-active');
    keypadIndex++;
    loginLed?.classList.add('is-pulsing');
    window.setTimeout(() => {
        keypadKeys.forEach((key) => key.classList.remove('is-active'));
        loginLed?.classList.remove('is-pulsing');
    }, 140);
}

function clearLoginValidation(input) {
    input?.closest('.login-form__field')?.classList.remove('has-error');
    input?.removeAttribute('aria-invalid');
    input?.closest('.login-form__field')?.querySelector('.login-inline-error')?.remove();
}

function showLoginValidation(input, message) {
    if (!input) return;
    const field = input.closest('.login-form__field');
    if (!field) return;
    field.classList.remove('has-error');
    void field.offsetWidth;
    field.classList.add('has-error');
    input.setAttribute('aria-invalid', 'true');
    let inlineError = field.querySelector('.login-inline-error');
    if (!inlineError) {
        inlineError = document.createElement('p');
        inlineError.className = 'login-inline-error';
        inlineError.setAttribute('role', 'alert');
        inlineError.setAttribute('aria-live', 'polite');
        field.append(inlineError);
    }
    inlineError.textContent = message;
}

[usernameInput, passwordInput].forEach((input) => {
    input?.addEventListener('focus', () => {
        input.closest('.login-input')?.classList.add('is-focused');
        loginLed?.classList.add('is-focused');
    });

    usernameInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            if (!passwordInput?.value) {
                passwordInput?.focus();
            } else {
                loginForm?.requestSubmit();
            }
        }
    });
    input?.addEventListener('blur', () => {
        input.closest('.login-input')?.classList.remove('is-focused');
        loginLed?.classList.remove('is-focused');
        if (input.value) clearLoginValidation(input);
    });
    input?.addEventListener('input', () => {
        pulseLoginKeypad();
        if (input.value) clearLoginValidation(input);
        if (!usernameInput?.value && !passwordInput?.value) {
            keypadKeys.forEach((key) => key.classList.remove('is-active'));
        }
    });
});

passwordInput?.addEventListener('keydown', (event) => {
    if (event.getModifierState?.('CapsLock')) {
        let badge = document.querySelector('.caps-lock-badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'caps-lock-badge';
            badge.setAttribute('role', 'status');
            badge.textContent = 'Caps Lock aktif';
            document.querySelector('[data-login-screen]')?.append(badge);
        }
        badge.hidden = false;
    }
});

passwordInput?.addEventListener('keyup', (event) => {
    const badge = document.querySelector('.caps-lock-badge');
    if (badge && event.getModifierState && !event.getModifierState('CapsLock')) {
        badge.hidden = true;
    }
});

function transitionAfterLogin(destination) {
    const transition = new CustomEvent('saji:login-success', {
        cancelable: true,
        detail: { destination },
    });
    if (window.dispatchEvent(transition)) window.location.assign(destination);
}

loginForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (loginForm.dataset.submitting === 'true') return;
    let firstInvalid = null;
    if (!usernameInput?.value.trim()) {
        showLoginValidation(usernameInput, 'Nama pengguna wajib diisi.');
        firstInvalid ??= usernameInput;
    }
    if (!passwordInput?.value) {
        showLoginValidation(passwordInput, 'Password wajib diisi.');
        firstInvalid ??= passwordInput;
    }
    if (firstInvalid) {
        firstInvalid.focus();
        return;
    }
    loginForm.dataset.submitting = 'true';
    const submit = loginForm.querySelector('[type="submit"]');
    setButtonLoading(submit, 'MEMPROSES...');
    try {
        const response = await fetch(loginForm.action, {
            method: 'POST',
            body: new FormData(loginForm),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            redirect: 'follow',
        });
        const url = new URL(response.url);
        if (url.pathname === '/login' || response.redirected && url.pathname === '/login') {
            let message = 'Username atau password tidak sesuai.';
            if (typeof response.text === 'function') {
                try {
                    const html = await response.clone().text();
                    const parsed = new DOMParser().parseFromString(html, 'text/html');
                    message = parsed.querySelector('.login-error')?.textContent?.trim() || message;
                } catch (_) {}
            }
            clearButtonLoading(submit);
            loginForm.dataset.submitting = 'false';
            const vending = document.querySelector('.vending-login');
            vending?.classList.remove('is-shaking');
            void vending?.offsetWidth;
            vending?.classList.add('is-shaking');
            window.setTimeout(() => vending?.classList.remove('is-shaking'), reduceMotion.matches ? 20 : 420);
            showToast(message, 'error');
            return;
        }
        transitionAfterLogin(url.pathname + url.search);
    } catch (error) {
        clearButtonLoading(submit);
        loginForm.dataset.submitting = 'false';
        const vending = document.querySelector('.vending-login');
        vending?.classList.remove('is-shaking');
        void vending?.offsetWidth;
        vending?.classList.add('is-shaking');
        window.setTimeout(() => vending?.classList.remove('is-shaking'), reduceMotion.matches ? 20 : 420);
        showToast('Username atau password tidak sesuai.', 'error');
    }
});

const logoutForm = document.querySelector('.sidebar__account form');
logoutForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = logoutForm.querySelector('button');
    if (button?.dataset.loading === 'true') return;
    if (button) button.dataset.loading = 'true';
    button?.setAttribute('aria-busy', 'true');
    button?.setAttribute('disabled', 'true');
    if (button) button.innerHTML = '<span class="button-spinner" aria-hidden="true"></span>';
    const overlay = document.createElement('div');
    overlay.className = 'logout-overlay';
    overlay.setAttribute('role', 'status');
    overlay.setAttribute('aria-live', 'polite');
    overlay.setAttribute('aria-busy', 'true');
    overlay.innerHTML = '<span class="logout-overlay__mark" aria-hidden="true">S</span><strong>Sedang keluar...</strong>';
    document.body.append(overlay);
    const started = performance.now();
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch(logoutForm.action, {
            method: 'POST',
            body: new FormData(logoutForm),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
            },
        });
        if (!response.ok) throw new Error('Logout gagal');
        const elapsed = performance.now() - started;
        await new Promise((resolve) => window.setTimeout(resolve, Math.max(0, 800 - elapsed)));
        overlay.classList.add('logout-overlay--leaving');
        await new Promise((resolve) => window.setTimeout(resolve, reduceMotion.matches ? 20 : 220));
        window.location.assign('/login');
    } catch (error) {
        overlay.remove();
        if (button) delete button.dataset.loading;
        button?.removeAttribute('disabled');
        button?.removeAttribute('aria-busy');
        if (button) button.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3" /><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7" /></svg>';
        showToast('Gagal keluar. Coba lagi.', 'error', 'Coba lagi', () => logoutForm.requestSubmit());
    }
});

window.addEventListener('offline', () => showToast('Koneksi terputus. Periksa jaringan Anda.', 'error'));
window.addEventListener('online', () => showToast('Koneksi kembali tersedia.', 'success'));

export function positionPopover(trigger, panel) {
    if (!trigger || !panel) return;
    panel.style.position = 'fixed';
    panel.style.zIndex = '20';
    document.body.append(panel);
    panel.hidden = false;
    const rect = trigger.getBoundingClientRect();
    const panelRect = panel.getBoundingClientRect();
    const margin = 12;
    let left = rect.right - panelRect.width;
    left = Math.max(margin, Math.min(left, window.innerWidth - panelRect.width - margin));
    let top = rect.bottom + 8;
    if (top + panelRect.height > window.innerHeight - margin) {
        top = Math.max(margin, rect.top - panelRect.height - 8);
    }
    panel.style.left = `${left}px`;
    panel.style.top = `${top}px`;
    panel.style.right = 'auto';
}

window.addEventListener('pageshow', () => {
    document.querySelector('.page-title')?.setAttribute('tabindex', '-1');
});

const initialLoader = document.querySelector('[data-initial-page-loader]');

if (initialLoader) {
    const hideInitialLoader = () => {
        window.setTimeout(() => {
            initialLoader.classList.add('initial-page-loader--leaving');
            window.setTimeout(() => initialLoader.remove(), reduceMotion.matches ? 20 : 280);
        }, 180);
    };

    if (document.readyState === 'complete') {
        hideInitialLoader();
    } else {
        window.addEventListener('load', hideInitialLoader, { once: true });
    }
}

const sidebar = document.querySelector('#operator-sidebar');
const sidebarOpen = document.querySelector('[data-sidebar-open]');
const sidebarClose = document.querySelector('[data-sidebar-close]');

const setSidebar = (open) => {
    if (!sidebar) return;
    document.body.classList.toggle('sidebar-open', open);
    sidebarOpen?.setAttribute('aria-expanded', String(open));
    if (open) {
        sidebar.querySelector('a[href]')?.focus();
    } else if (document.activeElement instanceof HTMLElement && sidebar.contains(document.activeElement)) {
        sidebarOpen?.focus();
    }
};

sidebarOpen?.addEventListener('click', () => setSidebar(true));
sidebarClose?.addEventListener('click', () => setSidebar(false));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setSidebar(false);
});
document.querySelectorAll('.sidebar__item').forEach((link) => {
    link.addEventListener('click', () => setSidebar(false));
});

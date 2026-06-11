const MAX_TOASTS = 4;

function getToastContainer() {
    let container = document.getElementById('admin-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'admin-toast-container';
        container.className = 'pointer-events-none fixed bottom-6 right-4 z-[9999] flex w-full max-w-sm flex-col items-end gap-3 sm:right-6';
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-relevant', 'additions');
        document.body.appendChild(container);
    }

    return container;
}

export function clearAdminToasts() {
    document.getElementById('admin-toast-container')?.replaceChildren();
}

export function showAdminToast(message, { variant = 'success', duration = 6000, subtitle = null } = {}) {
    const container = getToastContainer();

    while (container.children.length >= MAX_TOASTS) {
        container.firstElementChild?.remove();
    }

    const variants = {
        success: 'border-green-300 bg-green-50 text-green-900 shadow-green-100',
        error: 'border-red-300 bg-red-50 text-red-900 shadow-red-100',
        warning: 'border-amber-300 bg-amber-50 text-amber-900 shadow-amber-100',
        info: 'border-indigo-300 bg-indigo-50 text-indigo-900 shadow-indigo-100',
    };

    const toast = document.createElement('div');
    toast.setAttribute('data-admin-toast', '');
    toast.setAttribute('role', 'alert');
    toast.className = [
        'pointer-events-auto w-full rounded-xl border px-4 py-3 text-sm shadow-xl',
        'opacity-0 translate-y-2 transition-all duration-200 ease-out',
        variants[variant] ?? variants.info,
    ].join(' ');

    const title = document.createElement('p');
    title.className = 'font-semibold leading-snug';
    title.textContent = message;
    toast.appendChild(title);

    if (subtitle) {
        const body = document.createElement('p');
        body.className = 'mt-1 text-xs leading-snug opacity-90';
        body.textContent = subtitle;
        toast.appendChild(body);
    }

    container.appendChild(toast);

    const revealToast = () => {
        toast.classList.remove('opacity-0', 'translate-y-2');
        toast.classList.add('opacity-100', 'translate-y-0');
    };

    if (document.visibilityState === 'hidden') {
        revealToast();
    } else {
        requestAnimationFrame(revealToast);
    }

    window.setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', 'translate-y-2');
        window.setTimeout(() => toast.remove(), 220);
    }, duration);
}

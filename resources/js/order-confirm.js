function copyTextToClipboard(text) {
    if (navigator.clipboard?.writeText) {
        return navigator.clipboard.writeText(text);
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();

    try {
        document.execCommand('copy');
        return Promise.resolve();
    } finally {
        document.body.removeChild(textarea);
    }
}

let copyToastTimeout;
let copyButtonResetTimeout;

function setCopyButtonState(button, state) {
    const defaultIcon = button.querySelector('[data-copy-icon="default"]');
    const successIcon = button.querySelector('[data-copy-icon="success"]');

    if (state === 'success') {
        button.setAttribute('aria-copied', 'true');
        defaultIcon?.classList.add('hidden');
        successIcon?.classList.remove('hidden');
        return;
    }

    if (state === 'error') {
        button.setAttribute('aria-copied', 'false');
        defaultIcon?.classList.remove('hidden');
        successIcon?.classList.add('hidden');
        button.classList.add('border-red-300', 'bg-red-50', 'text-red-700');
        window.setTimeout(() => {
            button.classList.remove('border-red-300', 'bg-red-50', 'text-red-700');
        }, 2000);
        return;
    }

    button.setAttribute('aria-copied', 'false');
    defaultIcon?.classList.remove('hidden');
    successIcon?.classList.add('hidden');
}

function flashCopyButtonSuccess(button) {
    window.clearTimeout(copyButtonResetTimeout);
    setCopyButtonState(button, 'success');
    copyButtonResetTimeout = window.setTimeout(() => {
        setCopyButtonState(button, 'idle');
    }, 2000);
}

function showCopyToast(message, variant = 'success') {
    let container = document.getElementById('copy-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'copy-toast-container';
        container.className = 'pointer-events-none fixed bottom-6 left-1/2 z-[100] flex -translate-x-1/2 flex-col items-center gap-2';
        document.body.appendChild(container);
    }

    const existing = container.querySelector('[data-copy-toast]');
    if (existing) {
        existing.remove();
    }

    const toast = document.createElement('div');
    toast.setAttribute('data-copy-toast', '');
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    toast.className = [
        'pointer-events-auto rounded-xl border px-4 py-3 text-sm font-medium shadow-lg',
        'opacity-0 translate-y-2 transition-all duration-200 ease-out',
        variant === 'success'
            ? 'border-green-200 bg-green-50 text-green-800'
            : 'border-red-200 bg-red-50 text-red-800',
    ].join(' ');
    toast.textContent = message;
    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('opacity-0', 'translate-y-2');
        toast.classList.add('opacity-100', 'translate-y-0');
    });

    window.clearTimeout(copyToastTimeout);
    copyToastTimeout = window.setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', 'translate-y-2');
        window.setTimeout(() => toast.remove(), 200);
    }, 2500);
}

function isAndroidDevice() {
    return /Android/i.test(navigator.userAgent);
}

function isIosDevice() {
    return /iPhone|iPad|iPod/i.test(navigator.userAgent);
}

function readUpiPayConfig() {
    const configEl = document.getElementById('upi-pay-config');
    if (!configEl) {
        return null;
    }

    try {
        const config = JSON.parse(configEl.textContent);
        if (!config?.url || !config?.query) {
            return null;
        }

        return {
            url: config.url,
            query: config.query,
            apps: Array.isArray(config.apps) ? config.apps : [],
        };
    } catch {
        return null;
    }
}

function closeUpiAppSheet() {
    const sheet = document.getElementById('upi-app-sheet');
    if (!sheet) {
        return;
    }

    sheet.hidden = true;
    sheet.setAttribute('aria-hidden', 'true');
}

function showUpiAppSheet(apps) {
    const sheet = document.getElementById('upi-app-sheet');
    const list = sheet?.querySelector('[data-upi-app-list]');
    if (!sheet || !list || apps.length === 0) {
        return false;
    }

    list.replaceChildren();
    apps.forEach((app) => {
        const link = document.createElement('a');
        link.href = app.url;
        link.className = 'upi-app-sheet__option';
        link.textContent = app.label;
        link.addEventListener('click', () => closeUpiAppSheet());
        list.appendChild(link);
    });

    sheet.hidden = false;
    sheet.setAttribute('aria-hidden', 'false');

    return true;
}

function openUpiAppChooser(config) {
    if (!config?.url?.startsWith('upi://')) {
        return;
    }

    const { url, query, apps } = config;

    if (isAndroidDevice() && query) {
        window.location.href = `intent://pay?${query}#Intent;scheme=upi;end`;
        return;
    }

    if ((isIosDevice() || apps.length > 0) && showUpiAppSheet(apps)) {
        return;
    }

    window.location.href = url;
}

function initOrderConfirm() {
    const orderPlaced = document.querySelector('[data-order-placed]');
    if (orderPlaced) {
        orderPlaced.scrollIntoView({ behavior: 'smooth', block: 'center' });
        window.setTimeout(() => {
            orderPlaced.classList.remove('animate-pulse');
        }, 4000);
    }

    const upiSheet = document.getElementById('upi-app-sheet');
    if (upiSheet) {
        upiSheet.addEventListener('click', (event) => {
            if (event.target === upiSheet) {
                closeUpiAppSheet();
            }
        });

        upiSheet.querySelector('[data-upi-app-sheet-panel]')?.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        upiSheet.querySelector('[data-upi-app-sheet-close]')?.addEventListener('click', () => {
            closeUpiAppSheet();
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeUpiAppSheet();
        }
    });

    document.addEventListener('click', (event) => {
        const upiButton = event.target.closest('[data-upi-pay-button]');
        if (upiButton) {
            event.preventDefault();
            const config = readUpiPayConfig();
            if (config) {
                openUpiAppChooser(config);
            } else {
                const fallbackUrl = upiButton.getAttribute('href');
                if (fallbackUrl) {
                    window.location.href = fallbackUrl;
                }
            }
            return;
        }

        const button = event.target.closest('[data-copy-text]');
        if (!button) {
            return;
        }

        const text = button.getAttribute('data-copy-text');
        const defaultLabel = button.getAttribute('data-copy-label') || 'Copy';
        const toastMessage = button.getAttribute('data-copy-toast')
            || `${defaultLabel} — copied!`;

        copyTextToClipboard(text).then(() => {
            showCopyToast(toastMessage, 'success');
            flashCopyButtonSuccess(button);
        }).catch(() => {
            showCopyToast('Could not copy. Please try again.', 'error');
            setCopyButtonState(button, 'error');
        });
    });
}

document.addEventListener('DOMContentLoaded', initOrderConfirm);

export {
    initOrderConfirm,
    copyTextToClipboard,
    showCopyToast,
    openUpiAppChooser,
    closeUpiAppSheet,
    readUpiPayConfig,
};

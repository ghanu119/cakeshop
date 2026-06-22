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

function initOrderConfirm() {
    const orderPlaced = document.querySelector('[data-order-placed]');
    if (orderPlaced) {
        orderPlaced.scrollIntoView({ behavior: 'smooth', block: 'center' });
        window.setTimeout(() => {
            orderPlaced.classList.remove('animate-pulse');
        }, 4000);
    }

    document.addEventListener('click', (event) => {
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
};

import axios from 'axios';
import { showAdminToast } from './admin-toast';
import { resolveUserMessage, unwrapApiData } from './error-messages';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

function deleteUrl(template, token) {
    return template.replace('__TOKEN__', encodeURIComponent(token));
}

function normalizePreviewUrl(url) {
    if (!url) return '';
    if (url.startsWith('/')) return url;
    try {
        return new URL(url, window.location.origin).pathname;
    } catch {
        return url;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const root =
        document.getElementById('slider-item-image-manager') ||
        document.getElementById('home-slider-image-manager');
    if (!root) return;

    const maxBytes = parseInt(root.dataset.maxBytes, 10) || 2 * 1024 * 1024;
    const sizeExceededMessage =
        root.dataset.sizeExceededMessage || 'Image size exceeds the maximum allowed size.';
    const uploadUrl = root.dataset.uploadUrl;
    const deleteUrlTemplate = root.dataset.deleteUrlTemplate;
    const previewArea = root.querySelector('[data-role="preview-area"]');
    const previewFrame = root.querySelector('[data-role="preview-frame"]');
    const fileInput = root.querySelector('[data-role="file-input"]');
    const pickFileBtn = root.querySelector('[data-role="pick-file"]');
    const removeBtn = root.querySelector('[data-role="remove-image"]');
    const refInput = root.querySelector('[data-role="slide-image-ref"]');
    const removeFlagInput = root.querySelector('[data-role="remove-slide-image"]');
    const statusEl = root.querySelector('[data-role="status"]');

    let state = {
        ref: refInput?.value || null,
        token: null,
        url: '',
        fullUrl: '',
        name: '',
        kind: null,
        localUrl: null,
        uploading: false,
    };

    try {
        const existing = JSON.parse(root.dataset.existing || 'null');
        if (existing?.ref) {
            state = {
                ref: existing.ref,
                token: null,
                url: normalizePreviewUrl(existing.url),
                fullUrl: normalizePreviewUrl(existing.fullUrl || existing.url),
                name: existing.name || '',
                kind: 'existing',
                localUrl: null,
                uploading: false,
            };
        }
    } catch {
        // ignore
    }

    function setStatus(message, isError = false) {
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.classList.toggle('text-red-600', isError);
        statusEl.classList.toggle('text-gray-500', !isError);
    }

    function notifyError(message, { subtitle = null } = {}) {
        showAdminToast(message, { variant: 'error', subtitle });
        setStatus(message, true);
    }

    function syncFields() {
        if (refInput) {
            refInput.value = state.ref && !state.uploading ? state.ref : '';
        }
        if (removeFlagInput) {
            removeFlagInput.value = state.kind === 'removed' ? '1' : '0';
        }
    }

    function buildPreviewButton() {
        const fullSrc = state.fullUrl || state.url;
        const lightboxBtn = document.createElement('button');
        lightboxBtn.type = 'button';
        lightboxBtn.className = 'block h-full w-full';
        lightboxBtn.setAttribute('data-image-lightbox', '');
        lightboxBtn.setAttribute('data-full-src', fullSrc);
        lightboxBtn.setAttribute('data-alt', state.name || '');

        if (state.uploading) {
            lightboxBtn.innerHTML = `
                <div class="flex h-full w-full flex-col items-center justify-center gap-2 text-gray-500">
                    <div class="h-6 w-6 animate-spin rounded-full border-2 border-gray-300 border-t-amber-500"></div>
                    <span class="px-1 text-center text-[10px] font-medium">Uploading…</span>
                </div>
            `;
            return lightboxBtn;
        }

        const img = document.createElement('img');
        img.src = state.url;
        img.alt = state.name || '';
        img.className = 'h-full w-full object-cover';
        img.addEventListener('error', () => {
            lightboxBtn.textContent = '';
        });
        lightboxBtn.appendChild(img);

        return lightboxBtn;
    }

    function render() {
        if (!previewFrame || !previewArea) return;

        const hasPreview = state.uploading || (state.url && state.kind !== 'removed');

        previewArea.classList.toggle('hidden', !hasPreview);
        previewFrame.innerHTML = '';
        if (hasPreview) {
            previewFrame.appendChild(buildPreviewButton());
        }

        if (pickFileBtn) {
            pickFileBtn.disabled = state.uploading;
            pickFileBtn.textContent = hasPreview ? 'Replace image' : 'Choose image';
        }

        syncFields();

        if (!state.uploading && state.kind && state.kind !== 'removed') {
            setStatus(state.name || '');
        } else if (!state.uploading && state.kind === 'removed') {
            setStatus('');
        }
    }

    function clearLocalUrl() {
        if (state.localUrl) {
            URL.revokeObjectURL(state.localUrl);
            state.localUrl = null;
        }
    }

    async function removeImage() {
        if (state.uploading) return;

        if (state.kind === 'temp' && state.token) {
            try {
                await axios.delete(deleteUrl(deleteUrlTemplate, state.token), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                });
            } catch (err) {
                notifyError(resolveUserMessage(err));
                return;
            }
        }

        clearLocalUrl();
        state = {
            ref: null,
            token: null,
            url: '',
            fullUrl: '',
            name: '',
            kind: 'removed',
            localUrl: null,
            uploading: false,
        };
        render();
    }

    function rejectOversizedFile(file) {
        if (file.size <= maxBytes) {
            return false;
        }

        notifyError(sizeExceededMessage, { subtitle: file.name });
        return true;
    }

    async function uploadFile(file) {
        if (rejectOversizedFile(file)) {
            return;
        }

        if (state.kind === 'temp' && state.token) {
            await removeImage();
        }

        const localUrl = URL.createObjectURL(file);
        state = {
            ref: null,
            token: null,
            url: localUrl,
            fullUrl: localUrl,
            name: file.name,
            kind: 'temp',
            localUrl,
            uploading: true,
        };
        render();

        const formData = new FormData();
        formData.append('image', file);

        try {
            const { data } = await axios.post(uploadUrl, formData, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                    'Content-Type': 'multipart/form-data',
                },
            });

            const payload = unwrapApiData(data) ?? data;

            if (!payload?.token) {
                throw new Error('Upload response missing image token.');
            }

            clearLocalUrl();
            const previewUrl = normalizePreviewUrl(payload.url) || localUrl;
            state = {
                ref: `temp:${payload.token}`,
                token: payload.token,
                url: previewUrl,
                fullUrl: previewUrl,
                name: payload.name || file.name,
                kind: 'temp',
                localUrl: null,
                uploading: false,
            };
            render();
        } catch (err) {
            clearLocalUrl();
            state = {
                ref: null,
                token: null,
                url: '',
                fullUrl: '',
                name: '',
                kind: 'removed',
                localUrl: null,
                uploading: false,
            };
            render();
            notifyError(resolveUserMessage(err));
        }
    }

    pickFileBtn?.addEventListener('click', () => fileInput?.click());
    removeBtn?.addEventListener('click', () => removeImage());

    fileInput?.addEventListener('change', async (event) => {
        const file = (event.target.files || [])[0];
        event.target.value = '';
        if (!file) return;
        await uploadFile(file);
    });

    render();
});

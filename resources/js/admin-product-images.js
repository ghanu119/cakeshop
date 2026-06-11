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

function placeholderSvg() {
    return `<svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>`;
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('product-images-manager');
    if (!root) return;

    const maxImages = parseInt(root.dataset.maxImages, 10) || 10;
    const maxBytes = parseInt(root.dataset.maxBytes, 10) || 2 * 1024 * 1024;
    const sizeExceededMessage =
        root.dataset.sizeExceededMessage || 'Image size exceeds the maximum allowed size.';
    const uploadUrl = root.dataset.uploadUrl;
    const deleteUrlTemplate = root.dataset.deleteUrlTemplate;
    const grid = root.querySelector('[data-role="preview-grid"]');
    const fileInput = root.querySelector('[data-role="file-input"]');
    const pickFilesBtn = root.querySelector('[data-role="pick-files"]');
    const hiddenFields = root.querySelector('[data-role="hidden-fields"]');
    const statusEl = root.querySelector('[data-role="status"]');

    let items = [];
    try {
        items = JSON.parse(root.dataset.existing || '[]').map((row) => ({
            ref: row.ref,
            url: normalizePreviewUrl(row.url),
            fullUrl: normalizePreviewUrl(row.fullUrl || row.url),
            name: row.name,
            kind: 'existing',
            mediaId: row.id,
            uploading: false,
        }));
    } catch {
        items = [];
    }

    const removedMediaIds = new Set();

    function setStatus(message, isError = false) {
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.classList.toggle('text-red-600', isError);
        statusEl.classList.toggle('text-gray-500', !isError);
    }

    function notifyUploadError(message, { subtitle = null } = {}) {
        showAdminToast(message, { variant: 'error', subtitle });
        setStatus(message, true);
    }

    function primaryRef() {
        return items.find((item) => item.ref && !item.uploading)?.ref ?? items[0]?.ref ?? '';
    }

    function syncHiddenFields() {
        if (!hiddenFields) return;
        hiddenFields.innerHTML = '';
        items
            .filter((item) => item.ref && !item.uploading)
            .forEach((item) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'product_images[]';
                input.value = item.ref;
                hiddenFields.appendChild(input);
            });
        removedMediaIds.forEach((id) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'removed_media_ids[]';
            input.value = String(id);
            hiddenFields.appendChild(input);
        });
        const primary = primaryRef();
        if (primary) {
            const primaryInput = document.createElement('input');
            primaryInput.type = 'hidden';
            primaryInput.name = 'primary_image';
            primaryInput.value = primary;
            hiddenFields.appendChild(primaryInput);
        }
    }

    function moveItem(index, direction) {
        const target = index + direction;
        if (target < 0 || target >= items.length) return;
        const copy = [...items];
        [copy[index], copy[target]] = [copy[target], copy[index]];
        items = copy;
        render();
    }

    function setPrimary(index) {
        if (index <= 0 || index >= items.length) return;
        const copy = [...items];
        const [primary] = copy.splice(index, 1);
        items = [primary, ...copy];
        render();
    }

    async function removeItem(index) {
        const item = items[index];
        if (!item || item.uploading) return;

        if (item.kind === 'temp' && item.token) {
            try {
                await axios.delete(deleteUrl(deleteUrlTemplate, item.token), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                });
            } catch (err) {
                notifyUploadError(resolveUserMessage(err));
                return;
            }
        } else if (item.mediaId) {
            removedMediaIds.add(item.mediaId);
        }

        if (item.localUrl) {
            URL.revokeObjectURL(item.localUrl);
        }

        items = items.filter((_, i) => i !== index);
        render();
    }

    function buildImagePreview(item) {
        const frame = document.createElement('div');
        frame.className = 'h-full w-full overflow-hidden bg-gray-200';

        if (item.uploading) {
            frame.innerHTML = `
                <div class="flex h-full w-full flex-col items-center justify-center gap-2 text-gray-500">
                    <div class="h-6 w-6 animate-spin rounded-full border-2 border-gray-300 border-t-amber-500"></div>
                    <span class="px-1 text-center text-[10px] font-medium">Uploading…</span>
                </div>
            `;
            return frame;
        }

        const fullSrc = item.fullUrl || item.url;
        const lightboxBtn = document.createElement('button');
        lightboxBtn.type = 'button';
        lightboxBtn.setAttribute('data-image-lightbox', '');
        lightboxBtn.setAttribute('data-full-src', fullSrc);
        lightboxBtn.setAttribute('data-alt', item.name || '');

        const img = document.createElement('img');
        img.src = item.url;
        img.alt = item.name || '';
        img.loading = 'lazy';
        img.addEventListener('error', () => {
            lightboxBtn.innerHTML = `<div class="flex h-full w-full items-center justify-center">${placeholderSvg()}</div>`;
        });
        lightboxBtn.appendChild(img);

        const zoomHint = document.createElement('span');
        zoomHint.className = 'product-image-thumb__zoom-hint';
        zoomHint.setAttribute('aria-hidden', 'true');
        zoomHint.innerHTML = `<svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 016 0zM10 7a3 3 0 106 0 3 3 0 00-6 0z"/></svg>`;
        lightboxBtn.appendChild(zoomHint);

        frame.appendChild(lightboxBtn);

        return frame;
    }

    function render({ preserveErrorStatus = false } = {}) {
        if (!grid) return;
        grid.innerHTML = '';

        items.forEach((item, index) => {
            const card = document.createElement('div');
            card.className = 'product-image-thumb';
            const isPrimary = index === 0 && !item.uploading;

            card.appendChild(buildImagePreview(item));

            const controls = document.createElement('div');
            controls.className =
                'absolute inset-x-0 bottom-0 flex flex-wrap gap-1 bg-black/60 p-1';
            controls.addEventListener('click', (e) => e.stopPropagation());
            if (isPrimary) {
                const badge = document.createElement('span');
                badge.className =
                    'rounded bg-amber-500 px-2 py-0.5 text-[10px] font-bold uppercase text-white';
                badge.textContent = 'Primary';
                controls.appendChild(badge);
            }
            if (!item.uploading && index !== 0) {
                const primaryBtn = document.createElement('button');
                primaryBtn.type = 'button';
                primaryBtn.className =
                    'rounded bg-white/90 px-2 py-0.5 text-[10px] font-semibold text-gray-800';
                primaryBtn.textContent = 'Set primary';
                primaryBtn.addEventListener('click', () => setPrimary(index));
                controls.appendChild(primaryBtn);
            }
            if (!item.uploading && index > 0) {
                const upBtn = document.createElement('button');
                upBtn.type = 'button';
                upBtn.className =
                    'rounded bg-white/90 px-2 py-0.5 text-[10px] font-semibold text-gray-800';
                upBtn.textContent = '↑';
                upBtn.addEventListener('click', () => moveItem(index, -1));
                controls.appendChild(upBtn);
            }
            if (!item.uploading && index < items.length - 1) {
                const downBtn = document.createElement('button');
                downBtn.type = 'button';
                downBtn.className =
                    'rounded bg-white/90 px-2 py-0.5 text-[10px] font-semibold text-gray-800';
                downBtn.textContent = '↓';
                downBtn.addEventListener('click', () => moveItem(index, 1));
                controls.appendChild(downBtn);
            }
            if (!item.uploading) {
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className =
                    'rounded bg-red-600 px-2 py-0.5 text-[10px] font-semibold text-white';
                removeBtn.textContent = 'Remove';
                removeBtn.addEventListener('click', () => removeItem(index));
                controls.appendChild(removeBtn);
            }
            card.appendChild(controls);
            grid.appendChild(card);
        });

        syncHiddenFields();

        if (!preserveErrorStatus) {
            const readyCount = items.filter((item) => !item.uploading).length;
            setStatus(readyCount ? `${readyCount} / ${maxImages} images` : '');
        }

        if (fileInput) {
            fileInput.disabled = items.length >= maxImages || items.some((item) => item.uploading);
        }
    }

    function rejectOversizedFile(file) {
        if (file.size <= maxBytes) {
            return false;
        }

        notifyUploadError(sizeExceededMessage, { subtitle: file.name });
        return true;
    }

    async function uploadFile(file) {
        if (rejectOversizedFile(file)) {
            return false;
        }

        const localUrl = URL.createObjectURL(file);
        const pending = {
            ref: null,
            token: null,
            url: localUrl,
            fullUrl: localUrl,
            localUrl,
            name: file.name,
            kind: 'temp',
            uploading: true,
        };

        items.push(pending);
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

            const index = items.indexOf(pending);
            if (index === -1) {
                URL.revokeObjectURL(localUrl);
                return true;
            }

            const previewUrl = normalizePreviewUrl(payload.url) || localUrl;
            items[index] = {
                ref: `temp:${payload.token}`,
                token: payload.token,
                url: previewUrl,
                fullUrl: previewUrl,
                name: payload.name || file.name,
                kind: 'temp',
                uploading: false,
            };
            URL.revokeObjectURL(localUrl);
            render();
            return true;
        } catch (err) {
            items = items.filter((item) => item !== pending);
            URL.revokeObjectURL(localUrl);
            render({ preserveErrorStatus: true });
            notifyUploadError(resolveUserMessage(err));
            return false;
        }
    }

    pickFilesBtn?.addEventListener('click', () => fileInput?.click());

    fileInput?.addEventListener('change', async (event) => {
        const files = Array.from(event.target.files || []);
        event.target.value = '';

        if (!files.length) return;

        const slots = maxImages - items.filter((item) => !item.uploading).length;
        if (slots <= 0) {
            notifyUploadError(`Maximum ${maxImages} images reached.`);
            return;
        }

        const batch = files.slice(0, slots);
        let hadError = false;

        for (const file of batch) {
            const uploaded = await uploadFile(file);
            if (!uploaded) {
                hadError = true;
            }
        }

        if (!hadError) {
            const readyCount = items.filter((item) => !item.uploading).length;
            setStatus(readyCount ? `${readyCount} / ${maxImages} images` : '');
        }
    });

    render();
});

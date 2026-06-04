document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('image-lightbox');
    if (!modal) {
        return;
    }

    const backdrop = modal.querySelector('[data-image-lightbox-backdrop]');
    const closeBtn = modal.querySelector('[data-image-lightbox-close]');
    const prevBtn = modal.querySelector('[data-image-lightbox-prev]');
    const nextBtn = modal.querySelector('[data-image-lightbox-next]');
    const counterEl = modal.querySelector('[data-image-lightbox-counter]');
    const img = modal.querySelector('[data-image-lightbox-img]');

    let lastTrigger = null;
    let galleryItems = [];
    let currentIndex = 0;

    function parseGalleryItems(root) {
        if (!root) {
            return [];
        }
        const raw = root.getAttribute('data-image-lightbox-items');
        if (!raw) {
            return [];
        }
        try {
            const items = JSON.parse(raw);
            return Array.isArray(items) ? items : [];
        } catch {
            return [];
        }
    }

    function updateNav() {
        const hasMultiple = galleryItems.length > 1;
        prevBtn?.classList.toggle('hidden', !hasMultiple);
        nextBtn?.classList.toggle('hidden', !hasMultiple);
        counterEl?.classList.toggle('hidden', !hasMultiple);

        if (hasMultiple && counterEl) {
            counterEl.textContent = `${currentIndex + 1} / ${galleryItems.length}`;
        }
    }

    function showIndex(index) {
        if (!galleryItems.length || !img) {
            return;
        }
        const total = galleryItems.length;
        currentIndex = ((index % total) + total) % total;
        const item = galleryItems[currentIndex];
        img.src = item.src;
        img.alt = item.alt || '';
        updateNav();
    }

    function openFromTrigger(trigger) {
        const fullSrc = trigger.getAttribute('data-full-src');
        if (!fullSrc || !img) {
            return;
        }

        const galleryRoot = trigger.closest('[data-image-lightbox-items]');
        galleryItems = parseGalleryItems(galleryRoot);

        const index = parseInt(trigger.getAttribute('data-gallery-index'), 10);
        if (galleryItems.length > 0 && !Number.isNaN(index)) {
            currentIndex = index;
            showIndex(currentIndex);
        } else {
            galleryItems = [{ src: fullSrc, alt: trigger.getAttribute('data-alt') || '' }];
            currentIndex = 0;
            img.src = fullSrc;
            img.alt = trigger.getAttribute('data-alt') || '';
            updateNav();
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        closeBtn?.focus();
    }

    function close() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        galleryItems = [];
        currentIndex = 0;
        if (img) {
            img.removeAttribute('src');
            img.alt = '';
        }
        updateNav();
        lastTrigger?.focus();
        lastTrigger = null;
    }

    function step(direction) {
        if (galleryItems.length <= 1) {
            return;
        }
        showIndex(currentIndex + direction);
    }

    document.addEventListener('click', (event) => {
        const thumb = event.target.closest('[data-admin-ref-thumb]');
        if (thumb) {
            const gallery = thumb.closest('.admin-product-ref-gallery');
            const mainImg = gallery?.querySelector('.js-admin-ref-main-img');
            const mainBtn = gallery?.querySelector('.js-admin-ref-main');
            const mediumSrc = thumb.getAttribute('data-medium-src');
            const index = thumb.getAttribute('data-admin-ref-thumb');

            if (mediumSrc && mainImg) {
                mainImg.src = mediumSrc;
            }
            if (mainBtn && index !== null) {
                mainBtn.setAttribute('data-gallery-index', index);
                mainBtn.setAttribute('data-full-src', thumb.getAttribute('data-full-src') || '');
            }

            gallery?.querySelectorAll('[data-admin-ref-thumb]').forEach((el) => {
                el.classList.remove('border-indigo-500');
                el.classList.add('border-transparent');
            });
            thumb.classList.remove('border-transparent');
            thumb.classList.add('border-indigo-500');
        }

        const trigger = event.target.closest('[data-image-lightbox]');
        if (!trigger) {
            return;
        }
        event.preventDefault();
        lastTrigger = trigger;
        openFromTrigger(trigger);
    });

    backdrop?.addEventListener('click', close);
    closeBtn?.addEventListener('click', close);
    prevBtn?.addEventListener('click', (event) => {
        event.stopPropagation();
        step(-1);
    });
    nextBtn?.addEventListener('click', (event) => {
        event.stopPropagation();
        step(1);
    });

    document.addEventListener('keydown', (event) => {
        if (!modal.classList.contains('is-open')) {
            return;
        }
        if (event.key === 'Escape') {
            close();
            return;
        }
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            step(-1);
        }
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            step(1);
        }
    });
});

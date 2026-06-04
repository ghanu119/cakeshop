<div
    id="image-lightbox"
    class="image-lightbox"
    role="dialog"
    aria-modal="true"
    aria-label="{{ __('Full size image') }}"
    aria-hidden="true"
>
    <div class="image-lightbox__backdrop" data-image-lightbox-backdrop></div>
    <div class="image-lightbox__panel">
        <button
            type="button"
            class="image-lightbox__close"
            data-image-lightbox-close
            aria-label="{{ __('Close') }}"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <button
            type="button"
            class="image-lightbox__nav image-lightbox__nav--prev hidden"
            data-image-lightbox-prev
            aria-label="{{ __('Previous image') }}"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button
            type="button"
            class="image-lightbox__nav image-lightbox__nav--next hidden"
            data-image-lightbox-next
            aria-label="{{ __('Next image') }}"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
        <p class="image-lightbox__counter hidden" data-image-lightbox-counter aria-live="polite"></p>
        <img
            src=""
            alt=""
            class="image-lightbox__img"
            data-image-lightbox-img
        />
    </div>
</div>

document.addEventListener('DOMContentLoaded', () => {
    const typeRoot = document.getElementById('slider-item-type-fields');
    if (!typeRoot) return;

    const imageFields = document.querySelector('[data-role="image-fields"]');
    const videoFields = document.querySelector('[data-role="video-fields"]');
    const radios = typeRoot.querySelectorAll('[data-role="type-radio"]');

    function syncTypeFields() {
        const selected = typeRoot.querySelector('[data-role="type-radio"]:checked')?.value || 'image';
        imageFields?.classList.toggle('hidden', selected !== 'image');
        videoFields?.classList.toggle('hidden', selected !== 'video');
    }

    radios.forEach((radio) => radio.addEventListener('change', syncTypeFields));
    syncTypeFields();
});

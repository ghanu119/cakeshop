function initProductListAutoload() {
    const root = document.querySelector('[data-product-autoload]');

    if (!root) {
        return;
    }

    const grid = root.querySelector('[data-product-grid]');
    const sentinel = root.querySelector('[data-product-autoload-sentinel]');
    const status = root.querySelector('[data-product-autoload-status]');
    const statusLoader = root.querySelector('[data-product-autoload-loader]');
    const statusMessage = root.querySelector('[data-product-autoload-message]');
    const pagination = root.querySelector('[data-product-pagination]');

    if (!grid || !sentinel) {
        return;
    }

    let nextPageUrl = root.dataset.nextPageUrl || '';
    let isLoading = false;

    if (!nextPageUrl) {
        return;
    }

    if (pagination) {
        pagination.classList.add('hidden');
    }

    const updateStatus = ({ message = '', hidden = false, loading = false } = {}) => {
        if (!status) {
            return;
        }

        status.classList.toggle('hidden', hidden);

        if (statusMessage) {
            statusMessage.textContent = message;
        } else {
            status.textContent = message;
        }

        if (statusLoader) {
            statusLoader.classList.toggle('hidden', !loading);
        }
    };

    const sentinelWithinLoadRange = () => {
        if (!sentinel.isConnected) {
            return false;
        }

        const rect = sentinel.getBoundingClientRect();

        return rect.top <= window.innerHeight + 200;
    };

    const queueNextCheck = () => {
        window.requestAnimationFrame(() => {
            if (sentinelWithinLoadRange()) {
                void appendProducts();
            }
        });
    };

    const observer = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
            void appendProducts();
        }
    }, {
        rootMargin: '200px 0px',
    });

    const appendProducts = async () => {
        if (isLoading || !nextPageUrl) {
            return;
        }

        isLoading = true;
        updateStatus({
            message: 'Baking more cakes...',
            loading: true,
        });

        try {
            const url = new URL(nextPageUrl, window.location.origin);
            url.searchParams.set('autoload', '1');

            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Autoload failed with status ${response.status}`);
            }

            const payload = await response.json();

            if (payload.html) {
                grid.insertAdjacentHTML('beforeend', payload.html);
            }

            nextPageUrl = payload.next_page_url || '';
            root.dataset.nextPageUrl = nextPageUrl;

            if (!payload.has_more_pages || !nextPageUrl) {
                observer.disconnect();
                sentinel.remove();
                updateStatus({ hidden: true });

                return;
            }

            updateStatus({ hidden: true });
            queueNextCheck();
        } catch (error) {
            console.error(error);
            observer.disconnect();
            updateStatus({
                message: 'Could not load more cakes. Use pagination below to continue.',
            });

            if (pagination) {
                pagination.classList.remove('hidden');
            }
        } finally {
            isLoading = false;
        }
    };

    observer.observe(sentinel);
    queueNextCheck();
}

document.addEventListener('DOMContentLoaded', initProductListAutoload);

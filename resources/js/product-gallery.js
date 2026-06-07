import $ from 'jquery';
import 'slick-carousel';
import 'magnific-popup/dist/jquery.magnific-popup';
import 'magnific-popup/dist/magnific-popup.css';

function getSlickInstance($slider) {
    const element = $slider.get(0);
    return element?.slick ?? null;
}

function callSlickMethod($slider, method, ...args) {
    const instance = getSlickInstance($slider);
    if (!instance || typeof instance[method] !== 'function') {
        return false;
    }

    instance[method](...args);
    return true;
}

function parseGalleryItems($root) {
    const raw = $root.attr('data-gallery-items');
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

function openProductLightbox($root, startIndex) {
    const items = parseGalleryItems($root);
    if (!items.length) {
        return;
    }

    const popupItems = items.map((item) => ({
        src: item.src,
        type: 'image',
        title: item.title || '',
    }));

    $.magnificPopup.open({
        items: popupItems,
        type: 'image',
        gallery: {
            enabled: popupItems.length > 1,
            navigateByImgClick: true,
            preload: [0, 1],
        },
        mainClass: 'mfp-product-gallery',
        removalDelay: 200,
        closeOnContentClick: false,
        closeBtnInside: true,
        fixedContentPos: true,
        image: {
            verticalFit: true,
            titleSrc(item) {
                return item.title;
            },
        },
    }, startIndex);
}

function bindLightbox($root) {
    $root.off('click.productGalleryLightbox');
    $root.on('click.productGalleryLightbox', '.js-product-gallery-lightbox', function (e) {
        e.preventDefault();
        const index = parseInt($(this).data('galleryIndex'), 10);
        openProductLightbox($root, Number.isNaN(index) ? 0 : index);
    });
}

function syncProductGalleryLayout($root) {
    const width = $root.innerWidth();
    if (width <= 0) {
        return;
    }

    const isMobile = window.matchMedia('(max-width: 639px)').matches;
    const maxSide = isMobile
        ? Math.min(width, window.innerHeight * 0.5)
        : Math.min(width, 448);
    const side = Math.max(200, Math.round(maxSide));

    $root.css('--product-gallery-size', `${side}px`);
}

function initProductGalleryShell($root) {
    syncProductGalleryLayout($root);

    if ($root.hasClass('is-gallery-ready')) {
        return;
    }

    $root.addClass('is-gallery-ready');
}

function initOrderCompactGallery($gallery) {
    const $mainImg = $gallery.find('.js-order-gallery-main-img');
    const $mainLink = $gallery.find('.js-order-gallery-main');

    $gallery.on('click.orderCompactThumb', '[data-order-gallery-thumb]', function (e) {
        const $thumb = $(this);
        const mediumSrc = $thumb.data('medium-src');
        const index = parseInt($thumb.data('order-gallery-thumb'), 10);

        if (mediumSrc && $mainImg.length) {
            $mainImg.attr('src', mediumSrc);
        }
        if ($mainLink.length && !Number.isNaN(index)) {
            $mainLink.attr('data-gallery-index', index);
            const href = $thumb.attr('href');
            if (href) {
                $mainLink.attr('href', href);
            }
        }

        $gallery.find('[data-order-gallery-thumb]').removeClass('is-active').removeAttr('aria-current');
        $thumb.addClass('is-active').attr('aria-current', 'true');
    });
}

function initProductGallery() {
    const $roots = $('.js-product-gallery');
    if (!$roots.length) {
        return;
    }

    $roots.each(function () {
        const $gallery = $(this);
        bindLightbox($gallery);

        if ($gallery.hasClass('order-product-gallery--compact')) {
            initOrderCompactGallery($gallery);
            return;
        }

        initProductGalleryShell($gallery);

        const $main = $gallery.find('.js-product-gallery-main');
        const $thumbs = $gallery.parent().find('.js-product-gallery-thumbs');

        if (!$main.length || $main.children().length <= 1) {
            return;
        }

        if ($main.hasClass('slick-initialized')) {
            return;
        }

        $main.on('init', function (_event, slick) {
            initProductGalleryShell($gallery);
            if (slick && typeof slick.setPosition === 'function') {
                slick.setPosition();
            }
        });

        $main.slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            dots: false,
            fade: true,
            infinite: true,
            adaptiveHeight: false,
            swipe: true,
            responsive: [
                { breakpoint: 640, settings: { arrows: false, fade: false } },
            ],
        });

        $main.on('afterChange', function (_event, slick, currentSlide) {
            const slideCount = slick.slideCount;
            const normalizedIndex = slideCount > 0 ? currentSlide % slideCount : currentSlide;

            $thumbs.find('.product-gallery-thumb').removeClass('is-active border-amber-500').addClass('border-transparent');
            $thumbs.find(`[data-slide="${normalizedIndex}"]`).addClass('is-active border-amber-500').removeClass('border-transparent');
            syncProductGalleryLayout($gallery);
            if (slick && typeof slick.setPosition === 'function') {
                slick.setPosition();
            }
        });

        $thumbs.on('click', '[data-slide]', function () {
            const index = parseInt($(this).data('slide'), 10);
            if (!Number.isNaN(index)) {
                callSlickMethod($main, 'slickGoTo', index);
            }
        });
    });

    let resizeTimer;
    $(window).off('resize.productGallery').on('resize.productGallery', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            $roots.each(function () {
                const $root = $(this);
                syncProductGalleryLayout($root);
                const $slider = $root.find('.js-product-gallery-main');
                callSlickMethod($slider, 'setPosition');
            });
        }, 150);
    });
}

function syncCardDots($slider, slideIndex) {
    const $media = $slider.closest('.product-card-media');
    const $dots = $media.find('.product-card-dot');
    const count = $dots.length;
    if (!count) {
        return;
    }

    const normalizedIndex = count > 0 ? slideIndex % count : slideIndex;

    $dots.removeClass('is-active').attr('aria-selected', 'false');
    $dots.filter(`[data-slide="${normalizedIndex}"]`).addClass('is-active').attr('aria-selected', 'true');
}

function initCardSliders() {
    $('.js-product-card-slider').each(function () {
        const $slider = $(this);
        const slideCount = $slider.children('.product-card-slide').length;

        if (slideCount <= 1 || $slider.hasClass('slick-initialized')) {
            return;
        }

        const $media = $slider.closest('.product-card-media');
        const $dots = $media.find('.product-card-dot');
        let dragged = false;

        $slider.on('init', function (_event, slick) {
            $media.addClass('is-card-slider-ready');
            syncCardDots($slider, slick?.currentSlide ?? 0);
        });

        $slider.slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            dots: false,
            infinite: true,
            autoplay: true,
            autoplaySpeed: 3500,
            speed: 500,
            pauseOnHover: true,
            pauseOnFocus: true,
            pauseOnDotsHover: true,
            adaptiveHeight: false,
            swipe: true,
            touchThreshold: 8,
            fade: false,
        });

        $slider.on('swipe', function () {
            dragged = true;
        });

        $slider.on('afterChange', function (_event, slick, currentSlide) {
            const normalizedIndex = slick.slideCount > 0 ? currentSlide % slick.slideCount : currentSlide;
            syncCardDots($slider, normalizedIndex);
        });

        $dots.off('click.productCardDot').on('click.productCardDot', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const index = parseInt($(this).data('slide'), 10);
            if (!Number.isNaN(index)) {
                callSlickMethod($slider, 'slickGoTo', index);
            }
        });

        $slider.on('click', function (e) {
            if ($(e.target).closest('.product-card-dot').length) {
                return;
            }

            if (dragged) {
                dragged = false;
                e.preventDefault();
                return;
            }

            const url = $media.data('productUrl');
            if (url) {
                window.location.assign(url);
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    try {
        initProductGallery();
    } catch (error) {
        console.error('Product gallery initialization failed', error);
    }

    initCardSliders();
});

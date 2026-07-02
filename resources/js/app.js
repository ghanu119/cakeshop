import './bootstrap';
import './product-variant-picker';
import './flavor-picker';

import $ from 'jquery';
window.$ = window.jQuery = $;

import AOS from 'aos';
import 'aos/dist/aos.css';

import 'slick-carousel';
import 'slick-carousel/slick/slick.css';
import 'slick-carousel/slick/slick-theme.css';

import './product-gallery';
import './order-confirm';
import './product-list-autoload';

document.addEventListener('DOMContentLoaded', function () {
    AOS.init({
        duration: 600,
        easing: 'ease-out-cubic',
        once: true,
        offset: 40,
    });

    // Smooth scroll for anchor links
    $(document).on('click', 'a[href^="#"]', function (e) {
        const id = $(this).attr('href');
        if (id === '#') return;
        const $target = $(id);
        if ($target.length) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: $target.offset().top - 80 }, 400);
        }
    });

    // Product highlights carousel (home page) – only when element exists and has multiple children
    const $slider = $('.js-highlights-slider');
    if ($slider.length && $slider.children().length > 1) {
        $slider.slick({
            dots: true,
            arrows: true,
            infinite: true,
            speed: 400,
            slidesToShow: 4,
            slidesToScroll: 1,
            responsive: [
                { breakpoint: 1024, settings: { slidesToShow: 3 } },
                { breakpoint: 640, settings: { slidesToShow: 2, arrows: false } },
                { breakpoint: 480, settings: { slidesToShow: 1, arrows: false } },
            ],
        });
    }

    // Home hero slider (Better Buns) — fade carousel; init for 1+ slides
    const $homeSlider = $('.js-home-slider');
    if ($homeSlider.length && $homeSlider.children('.home-slider-slide').length >= 1) {
        if ($homeSlider.children('.home-slider-slide').length > 1) {
            $homeSlider.slick({
                dots: true,
                arrows: true,
                infinite: true,
                fade: true,
                speed: 600,
                autoplay: true,
                autoplaySpeed: 5000,
                pauseOnHover: true,
                adaptiveHeight: false,
            });
        }
    }
});

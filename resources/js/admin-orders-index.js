import $ from 'jquery';

const TODAY_VIEW = 'today';

function isTodayView() {
    return new URLSearchParams(window.location.search).get('view') === TODAY_VIEW;
}

function cleanTodayUrl() {
    if (!isTodayView()) {
        return;
    }

    const url = new URL(window.location.href);

    if (url.searchParams.get('view') !== TODAY_VIEW) {
        return;
    }

    if ([...url.searchParams.keys()].every((key) => key === 'view')) {
        return;
    }

    history.replaceState(null, '', `${url.pathname}?view=${TODAY_VIEW}`);
}

function parseOrdersIndexParams(href) {
    const url = new URL(href, window.location.origin);
    const params = {};

    url.searchParams.forEach((value, key) => {
        params[key] = value;
    });

    return params;
}

function isOrdersIndexLink(href) {
    if (!href) {
        return false;
    }

    try {
        const url = new URL(href, window.location.origin);

        return url.pathname.replace(/\/+$/, '') === new URL($('#admin-orders-results').data('orders-url'), window.location.origin).pathname.replace(/\/+$/, '');
    } catch {
        return false;
    }
}

function loadTodayOrders(params = {}) {
    const $container = $('#admin-orders-results');
    const url = $container.data('orders-url');

    if (!url) {
        return $.Deferred().reject().promise();
    }

    $container.addClass('pointer-events-none opacity-60');

    return $.ajax({
        url,
        data: {
            delivery_today: 1,
            sort: params.sort || 'delivery_at',
            direction: params.direction || 'asc',
            page: params.page || undefined,
        },
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'text/html',
        },
    })
        .done((html) => {
            $container.html(html);
        })
        .always(() => {
            $container.removeClass('pointer-events-none opacity-60');
        });
}

$(function () {
    const $container = $('#admin-orders-results');

    if (!$container.length || String($container.data('today-view')) !== '1') {
        return;
    }

    cleanTodayUrl();

    $container.on('click', 'a[href]', function (event) {
        const href = $(this).attr('href');

        if (!isOrdersIndexLink(href)) {
            return;
        }

        event.preventDefault();

        const params = parseOrdersIndexParams(href);
        loadTodayOrders(params);
    });
});

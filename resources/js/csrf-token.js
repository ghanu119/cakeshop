function readCsrfFromCookie() {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    if (!match?.[1]) {
        return null;
    }

    try {
        return decodeURIComponent(match[1]);
    } catch {
        return null;
    }
}

export function refreshCsrfToken() {
    const token = readCsrfFromCookie();

    if (!token) {
        return null;
    }

    const meta = document.head.querySelector('meta[name="csrf-token"]');

    if (meta) {
        meta.setAttribute('content', token);
    }

    return token;
}

export function getCsrfToken() {
    const metaToken = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (metaToken) {
        return metaToken;
    }

    return readCsrfFromCookie() ?? '';
}

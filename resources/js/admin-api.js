import axios from 'axios';

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfMeta = document.head.querySelector('meta[name="csrf-token"]');
if (csrfMeta?.content) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfMeta.content;
}

const USER_MESSAGES = {
    offline: "You're offline. Live updates paused.",
    server: 'Something went wrong. You can keep working — please try again.',
    session: 'Your session expired. Please sign in again.',
    forbidden: "You don't have permission to do that.",
};

function resolveUserMessage(error) {
    if (!navigator.onLine) {
        return USER_MESSAGES.offline;
    }

    const response = error?.response;
    if (response?.data?.message) {
        return response.data.message;
    }

    if (response?.status === 401) {
        return USER_MESSAGES.session;
    }

    if (response?.status === 403) {
        return USER_MESSAGES.forbidden;
    }

    if (response?.status === 419) {
        return 'Your session expired. Please refresh the page and try again.';
    }

    if (response?.status === 422) {
        return response?.data?.message ?? USER_MESSAGES.server;
    }

    if (response?.status >= 500) {
        return USER_MESSAGES.server;
    }

    return USER_MESSAGES.server;
}

export async function adminApi(method, url, data = undefined) {
    try {
        const response = await axios({
            method,
            url,
            data,
            withCredentials: true,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfMeta?.content ?? axios.defaults.headers.common['X-CSRF-TOKEN'] ?? '',
            },
        });

        if (response.data?.success === false) {
            return {
                ok: false,
                message: response.data.message ?? USER_MESSAGES.server,
                data: response.data.data ?? null,
                status: response.status,
            };
        }

        return {
            ok: true,
            data: response.data?.data ?? response.data,
            message: response.data?.message ?? null,
            status: response.status,
        };
    } catch (error) {
        const message = resolveUserMessage(error);
        const status = error?.response?.status ?? 0;

        if (status === 401) {
            window.setTimeout(() => {
                window.location.href = '/admin/login';
            }, 1500);
        }

        return {
            ok: false,
            message,
            data: null,
            status,
        };
    }
}

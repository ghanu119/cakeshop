import axios from 'axios';
import { extractValidationErrors, getAppMessages, resolveUserMessage } from './error-messages';

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfMeta = document.head.querySelector('meta[name="csrf-token"]');

function readCsrfFromCookie() {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    if (!match?.[1]) {
        return null;
    }

    try {
        return decodeURIComponent(match[1]);
    } catch (error) {
        console.warn('Could not decode CSRF cookie', error);

        return null;
    }
}

function applyCsrfToken(token) {
    if (!token) {
        return false;
    }

    if (csrfMeta) {
        csrfMeta.content = token;
    }

    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

    return true;
}

function refreshCsrfToken() {
    return applyCsrfToken(readCsrfFromCookie());
}

if (csrfMeta?.content) {
    applyCsrfToken(csrfMeta.content);
}

export async function adminApi(method, url, data = undefined, { preventNavigation = false } = {}) {
    const requestConfig = {
        method,
        url,
        data,
        withCredentials: true,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfMeta?.content ?? axios.defaults.headers.common['X-CSRF-TOKEN'] ?? '',
        },
    };

    try {
        const response = await axios(requestConfig);

        if (response.data?.success === false) {
            return {
                ok: false,
                message: response.data.message ?? getAppMessages().server,
                data: response.data.data ?? null,
                errors: extractValidationErrors({ response }) ?? null,
                code: response.data.code ?? response.data.data?.code ?? null,
                status: response.status,
            };
        }

        return {
            ok: true,
            data: response.data?.data ?? response.data,
            message: response.data?.message ?? null,
            errors: null,
            code: null,
            status: response.status,
        };
    } catch (error) {
        const message = resolveUserMessage(error);
        const status = error?.response?.status ?? 0;
        const code = error?.response?.data?.code ?? error?.response?.data?.data?.code ?? null;

        if (status === 419 && refreshCsrfToken()) {
            requestConfig.headers['X-CSRF-TOKEN'] = csrfMeta?.content ?? axios.defaults.headers.common['X-CSRF-TOKEN'] ?? '';

            try {
                const retryResponse = await axios(requestConfig);

                if (retryResponse.data?.success === false) {
                    return {
                        ok: false,
                        message: retryResponse.data.message ?? getAppMessages().server,
                        data: retryResponse.data.data ?? null,
                        errors: extractValidationErrors({ response: retryResponse }) ?? null,
                        code: retryResponse.data.code ?? retryResponse.data.data?.code ?? null,
                        status: retryResponse.status,
                    };
                }

                return {
                    ok: true,
                    data: retryResponse.data?.data ?? retryResponse.data,
                    message: retryResponse.data?.message ?? null,
                    errors: null,
                    code: null,
                    status: retryResponse.status,
                };
            } catch (retryError) {
                return {
                    ok: false,
                    message: resolveUserMessage(retryError),
                    data: null,
                    errors: extractValidationErrors(retryError),
                    code: retryError?.response?.data?.code ?? retryError?.response?.data?.data?.code ?? null,
                    status: retryError?.response?.status ?? 0,
                };
            }
        }

        if (!preventNavigation) {
            if (status === 401) {
                window.setTimeout(() => {
                    window.location.href = '/admin/login';
                }, 1500);
            }

            if (status === 419) {
                window.setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        }

        return {
            ok: false,
            message,
            data: null,
            errors: extractValidationErrors(error),
            code,
            status,
        };
    }
}

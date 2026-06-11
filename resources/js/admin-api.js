import axios from 'axios';
import { extractValidationErrors, getAppMessages, resolveUserMessage } from './error-messages';

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfMeta = document.head.querySelector('meta[name="csrf-token"]');
if (csrfMeta?.content) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfMeta.content;
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

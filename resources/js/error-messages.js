const DEFAULT_MESSAGES = {
    csrf_expired: 'Your session expired. Please refresh the page and try again.',
    session_expired: 'Please sign in again to continue.',
    forbidden: "You don't have permission to do that.",
    validation_failed: 'Validation failed.',
    not_found: "We couldn't find that page.",
    database: "We're having trouble saving your data right now. Please try again in a moment.",
    server: 'Something went wrong on our end. Please try again.',
    too_many_requests: 'Too many attempts. Please wait a minute and try again.',
    maintenance: "We're doing some maintenance. Please check back shortly.",
    offline: "You're offline. Check your connection and try again.",
};

export function getAppMessages() {
    return {
        ...DEFAULT_MESSAGES,
        ...(window.__appMessages ?? {}),
    };
}

export function extractValidationErrors(error) {
    const errors = error?.response?.data?.data?.errors ?? error?.response?.data?.errors;

    if (!errors || typeof errors !== 'object') {
        return null;
    }

    return errors;
}

export function firstValidationMessage(error) {
    const errors = extractValidationErrors(error);

    if (!errors) {
        return null;
    }

    for (const field of Object.keys(errors)) {
        const messages = errors[field];
        if (Array.isArray(messages) && messages.length > 0) {
            return messages[0];
        }
    }

    return null;
}

export function resolveUserMessage(error) {
    const messages = getAppMessages();

    if (!navigator.onLine) {
        return messages.offline;
    }

    const response = error?.response;
    const code = response?.data?.code ?? response?.data?.data?.code;

    if (code && messages[code]) {
        return messages[code];
    }

    if (response?.data?.message) {
        return response.data.message;
    }

    const validationMessage = firstValidationMessage(error);
    if (validationMessage) {
        return validationMessage;
    }

    if (response?.status === 401) {
        return messages.session_expired;
    }

    if (response?.status === 403) {
        return messages.forbidden;
    }

    if (response?.status === 419) {
        return messages.csrf_expired;
    }

    if (response?.status === 404) {
        return messages.not_found;
    }

    if (response?.status === 422) {
        return messages.validation_failed;
    }

    if (response?.status === 429) {
        return messages.too_many_requests;
    }

    if (response?.status === 503) {
        return messages.maintenance;
    }

    if (response?.status >= 500) {
        return code === 'database' ? messages.database : messages.server;
    }

    return messages.server;
}

export function unwrapApiData(responseData) {
    if (responseData && typeof responseData === 'object' && 'success' in responseData) {
        return responseData.data ?? null;
    }

    return responseData ?? null;
}

import { adminApi } from './admin-api';
import { unlockNotificationSound, playNewOrderSound } from './admin-notification-sound';
import { clearAdminToasts, showAdminToast } from './admin-toast';
import { createAdminEcho } from './echo';

const COUNT_POLL_INTERVAL_MS = 20000;
const ORDER_SOUND_TYPES = new Set(['new_order', 'kitchen_order_queued_today', 'kitchen_payment_verified_today']);
const routes = window.__notificationRoutes ?? {};

let pageLoadMs = Date.now();
let echo = null;
let isPollingFallback = false;
let countPollTimer = null;
let connectTimeout = null;
let lastUnreadCount = null;
let fetchErrorShown = false;
let consecutivePollingAuthFailures = 0;
let disconnectWarningShown = false;
let wasLiveConnected = false;
const liveToastedIds = new Set();

const CONNECT_TIMEOUT_MS = 12000;
const PUSH_BANNER_DISMISS_KEY = 'staff_push_banner_dismissed';
const PUSH_SETUP_TOAST_SHOWN_KEY = 'staff_push_setup_toast_shown';
const SESSION_TOASTED_IDS_KEY = 'staff_notification_toasted_ids';
const SESSION_TOASTED_IDS_LIMIT = 200;
let pushSubscribeInFlight = null;

function isAdminHttp() {
    return location.protocol === 'http:' && /\.(test|localhost)$/i.test(location.hostname);
}

const POLL_OPTIONS = { preventNavigation: true };
const POLLING_AUTH_FAILURE_REDIRECT_AT = 2;

function isPollingAuthFailure(result) {
    return result.code === 'session_expired'
        || result.code === 'csrf_expired'
        || result.status === 401
        || result.status === 419;
}

function stopCountPolling() {
    if (countPollTimer) {
        window.clearInterval(countPollTimer);
        countPollTimer = null;
    }
}

function handlePollingAuthFailure(result) {
    stopCountPolling();

    if (result.code === 'csrf_expired' || result.status === 419) {
        window.location.reload();
        return;
    }

    window.location.href = '/admin/login';
}

function httpsAdminUrl() {
    if (location.protocol === 'https:') {
        return location.href;
    }

    return `https://${location.hostname}${location.pathname}${location.search}${location.hash}`;
}

function resolveNotificationUrl(rawUrl) {
    const fallback = '/admin/dashboard';

    if (!rawUrl || rawUrl === '#') {
        return fallback;
    }

    try {
        const target = rawUrl.startsWith('/')
            ? new URL(rawUrl, location.origin)
            : new URL(rawUrl);

        if (!target.pathname.startsWith('/admin')) {
            return fallback;
        }

        return `${target.pathname}${target.search}${target.hash}`;
    } catch (error) {
        return fallback;
    }
}

function redirectToHttpsAdmin() {
    if (!isAdminHttp()) {
        return false;
    }

    location.replace(httpsAdminUrl());

    return true;
}

function canUseBrowserAlerts() {
    return window.isSecureContext === true && !isAdminHttp();
}

async function isPushSubscribed() {
    if (!canUseBrowserAlerts() || !('serviceWorker' in navigator) || !('PushManager' in window)) {
        return false;
    }

    try {
        const registration = await navigator.serviceWorker.getRegistration('/sw.js');

        if (!registration) {
            return false;
        }

        const subscription = await registration.pushManager.getSubscription();

        return subscription !== null;
    } catch (error) {
        console.warn('Could not read push subscription', error);

        return false;
    }
}

function showPushPermissionBanner() {
    const banner = document.querySelector('[data-push-permission-banner]');
    if (!banner || localStorage.getItem(PUSH_BANNER_DISMISS_KEY) === '1') {
        return;
    }

    banner.classList.remove('hidden');
    banner.classList.add('flex');
}

function hidePushPermissionBanner() {
    const banner = document.querySelector('[data-push-permission-banner]');
    if (!banner) {
        return;
    }

    banner.classList.add('hidden');
    banner.classList.remove('flex');
}

function hasShownPushSetupToast() {
    return localStorage.getItem(PUSH_SETUP_TOAST_SHOWN_KEY) === '1';
}

function markPushSetupToastShown() {
    localStorage.setItem(PUSH_SETUP_TOAST_SHOWN_KEY, '1');
}

function clearPushSetupToastShown() {
    localStorage.removeItem(PUSH_SETUP_TOAST_SHOWN_KEY);
}

function updatePushDeviceStatus(message, tone = 'neutral') {
    const statusEl = document.querySelector('[data-push-device-status]');
    const badgeEl = document.querySelector('[data-push-device-status-badge]');

    if (statusEl) {
        statusEl.textContent = message;
    }

    if (!badgeEl) {
        return;
    }

    badgeEl.classList.remove('bg-emerald-100', 'text-emerald-800', 'bg-amber-100', 'text-amber-800', 'bg-red-100', 'text-red-800', 'bg-gray-100', 'text-gray-600');

    if (tone === 'success') {
        badgeEl.textContent = 'Ready on this device';
        badgeEl.classList.add('bg-emerald-100', 'text-emerald-800');
    } else if (tone === 'warning') {
        badgeEl.textContent = 'Setup needed on this device';
        badgeEl.classList.add('bg-amber-100', 'text-amber-800');
    } else if (tone === 'error') {
        badgeEl.textContent = 'Blocked in browser';
        badgeEl.classList.add('bg-red-100', 'text-red-800');
    } else {
        badgeEl.textContent = 'Setup needed on this device';
        badgeEl.classList.add('bg-amber-100', 'text-amber-800');
    }
}

function updatePushButtonState({ subscribed = false } = {}) {
    const buttons = document.querySelectorAll('[data-enable-push]');
    const labels = document.querySelectorAll('[data-enable-push-label]');
    const testButtons = document.querySelectorAll('[data-test-push]');

    if (!buttons.length || !labels.length) {
        return;
    }

    testButtons.forEach((testButton) => {
        testButton.classList.toggle('hidden', !subscribed);
    });

    if (!canUseBrowserAlerts()) {
        labels.forEach((label) => {
            label.textContent = 'Open HTTPS admin';
        });
        updatePushDeviceStatus(`You are on http://. Open ${httpsAdminUrl()} then allow notifications again.`, 'error');
        return;
    }

    if (Notification.permission === 'denied') {
        labels.forEach((label) => {
            label.textContent = 'Alerts blocked';
        });
        updatePushDeviceStatus(`Notifications are blocked in Chrome. Open site settings for ${location.hostname} → Notifications → Allow.`, 'error');
        return;
    }

    if (subscribed) {
        labels.forEach((label) => {
            label.textContent = 'Browser alerts on';
        });
        updatePushDeviceStatus('Registered. Tab open = Windows popup + in-app toast. Tab minimized = Windows popup from background.', 'success');
        return;
    }

    if (Notification.permission === 'granted') {
        labels.forEach((label) => {
            label.textContent = 'Finish setup';
        });
        updatePushDeviceStatus('Chrome permission is on, but this device is not registered yet. Click the button once to finish.', 'warning');
        return;
    }

    labels.forEach((label) => {
        label.textContent = 'Allow browser notifications';
    });
    updatePushDeviceStatus('Click Allow browser notifications, then choose Allow in the Chrome prompt.', 'warning');
}

function isTabVisible() {
    return document.visibilityState === 'visible';
}

function showOsNotificationFromPage(item) {
    if (!canUseBrowserAlerts() || Notification.permission !== 'granted' || typeof Notification === 'undefined') {
        return false;
    }

    try {
        const tag = item?.id ? `staff-${item.id}` : 'staff-alert';
        const notification = new Notification(item?.title ?? 'Notification', {
            body: item?.message ?? '',
            icon: '/favicon.ico',
            tag,
        });

        notification.onclick = () => {
            window.focus();
            const url = resolveNotificationUrl(item?.url);
            if (url && url !== '#') {
                window.location.href = url;
            }
            notification.close();
        };

        return true;
    } catch (error) {
        console.warn('OS notification failed', error);

        return false;
    }
}

function parseInstant(value) {
    if (!value) {
        return null;
    }

    const ms = Date.parse(value);

    return Number.isNaN(ms) ? null : ms;
}

function isNewSincePageLoad(item) {
    const itemMs = parseInstant(item?.created_at);

    if (itemMs === null) {
        return false;
    }

    return itemMs > pageLoadMs;
}

function loadSessionToastedIds() {
    try {
        const raw = sessionStorage.getItem(SESSION_TOASTED_IDS_KEY);
        if (!raw) {
            return [];
        }

        const parsed = JSON.parse(raw);

        return Array.isArray(parsed) ? parsed.filter(Boolean) : [];
    } catch (error) {
        return [];
    }
}

function persistToastedId(id) {
    if (!id) {
        return;
    }

    try {
        const ids = new Set([...loadSessionToastedIds(), ...liveToastedIds, id]);
        sessionStorage.setItem(
            SESSION_TOASTED_IDS_KEY,
            JSON.stringify([...ids].slice(-SESSION_TOASTED_IDS_LIMIT))
        );
    } catch (error) {
        // sessionStorage may be unavailable in private mode
    }
}

function seedSeenNotificationIds() {
    const ids = new Set([
        ...getKnownNotificationIds(),
        ...loadSessionToastedIds(),
    ]);

    ids.forEach((id) => liveToastedIds.add(id));

    try {
        sessionStorage.setItem(
            SESSION_TOASTED_IDS_KEY,
            JSON.stringify([...ids].slice(-SESSION_TOASTED_IDS_LIMIT))
        );
    } catch (error) {
        // sessionStorage may be unavailable in private mode
    }
}

function shouldPlayOrderSound(item) {
    return ORDER_SOUND_TYPES.has(item?.type);
}

function toastNotificationItem(item, { force = false } = {}) {
    if (!item?.title || (!force && !isNewSincePageLoad(item))) {
        return;
    }

    const id = item.id;

    if (id && liveToastedIds.has(id)) {
        return;
    }

    if (id) {
        liveToastedIds.add(id);
        persistToastedId(id);
    }

    if (shouldPlayOrderSound(item)) {
        void playNewOrderSound();
    }

    showAdminToast(item.title, {
        variant: 'info',
        duration: 7000,
        subtitle: item.message || null,
    });
}

function updateBadge(count) {
    const badge = document.querySelector('[data-notification-badge]');
    if (!badge) return;

    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : String(count);
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

function setConnectionState(state, { showDisconnectToast = false } = {}) {
    const dot = document.querySelector('[data-notification-connection]');
    if (!dot) return;

    dot.classList.remove('bg-emerald-500', 'bg-amber-400', 'bg-gray-300', 'animate-pulse');
    if (state === 'connected') {
        dot.classList.add('bg-emerald-500');
        dot.title = 'Live updates active';
    } else if (state === 'connecting') {
        dot.classList.add('bg-amber-400', 'animate-pulse');
        dot.title = 'Connecting to live updates…';
    } else if (state === 'polling') {
        dot.classList.add('bg-amber-400');
        dot.title = 'Checking for new notifications every 20 seconds';
        if (showDisconnectToast && !disconnectWarningShown) {
            disconnectWarningShown = true;
            showAdminToast('Live updates paused — checking every 20 seconds.', { variant: 'warning' });
        }
    } else {
        dot.classList.add('bg-gray-300');
        dot.title = 'Notifications via saved list';
    }
}

function getUnreadListItemCount() {
    return document.querySelectorAll('[data-notification-list] [data-notification-id]').length;
}

function getKnownNotificationIds() {
    return new Set(
        [...document.querySelectorAll('[data-notification-list] [data-notification-id]')]
            .map((el) => el.getAttribute('data-notification-id'))
            .filter(Boolean)
    );
}

function buildNotificationListItem(item) {
    const li = document.createElement('li');
    const url = resolveNotificationUrl(item.url);
    li.setAttribute('data-notification-id', item.id);
    li.innerHTML = `
        <a href="${url}" class="block px-4 py-3 hover:bg-gray-50" data-notification-link>
            <p class="text-sm font-semibold text-gray-900">${item.title ?? ''}</p>
            <p class="mt-0.5 text-xs text-gray-600">${item.message ?? ''}</p>
            <p class="mt-1 text-[10px] text-gray-400">${item.created_human ?? ''}</p>
        </a>
    `;

    return li;
}

function renderNotificationList(items) {
    const list = document.querySelector('[data-notification-list]');
    if (!list) {
        return;
    }

    list.replaceChildren();

    if (!items.length) {
        const empty = document.createElement('li');
        empty.setAttribute('data-notification-empty', '');
        empty.className = 'px-4 py-6 text-center text-sm text-gray-500';
        empty.textContent = 'No unread notifications';
        list.appendChild(empty);
        return;
    }

    items.forEach((item) => {
        list.appendChild(buildNotificationListItem(item));

        if (item.highlight_target) {
            document.querySelectorAll(`[data-highlight-target="${item.highlight_target}"]`).forEach((el) => {
                el.classList.add('notification-highlight');
            });
        }
    });
}

function prependNotificationItem(item) {
    const list = document.querySelector('[data-notification-list]');
    if (!list || !item) return;

    const empty = list.querySelector('[data-notification-empty]');
    if (empty) empty.remove();

    const existing = list.querySelector(`[data-notification-id="${item.id}"]`);
    if (existing) return;

    list.prepend(buildNotificationListItem(item));

    if (item.highlight_target) {
        document.querySelectorAll(`[data-highlight-target="${item.highlight_target}"]`).forEach((el) => {
            el.classList.add('notification-highlight');
        });
    }
}

async function fetchUnreadList() {
    if (!routes.index) {
        return [];
    }

    const result = await adminApi('get', `${routes.index}?unread_only=1`, undefined, POLL_OPTIONS);

    if (!result.ok) {
        return [];
    }

    const items = result.data?.items ?? [];
    renderNotificationList(items);

    return items;
}

async function syncUnreadNotifications({ toast = false } = {}) {
    const knownIds = getKnownNotificationIds();
    const items = await fetchUnreadList();

    if (toast) {
        items
            .filter((item) => !knownIds.has(item.id))
            .forEach((item) => toastNotificationItem(item));
    }

    return items;
}

async function syncNotificationListWithBadge() {
    const count = await refreshUnreadCount();

    if (count === null) {
        return;
    }

    const listCount = getUnreadListItemCount();

    if (count === 0) {
        if (listCount > 0) {
            renderNotificationList([]);
        }
        return;
    }

    if (listCount === 0 || listCount < count) {
        await fetchUnreadList();
    }
}

function applyHighlightTargets(targets) {
    document.querySelectorAll('[data-highlight-target]').forEach((el) => {
        const target = el.getAttribute('data-highlight-target');
        if (targets.includes(target)) {
            el.classList.add('notification-highlight');
        }
    });
}

async function refreshUnreadCount() {
    if (!routes.unreadCount) return null;

    const result = await adminApi('get', routes.unreadCount, undefined, POLL_OPTIONS);
    if (result.ok && result.data?.count !== undefined) {
        consecutivePollingAuthFailures = 0;
        updateBadge(result.data.count);
        fetchErrorShown = false;
        hideInlineError();
        return result.data.count;
    }

    if (isPollingAuthFailure(result)) {
        consecutivePollingAuthFailures += 1;

        if (consecutivePollingAuthFailures >= POLLING_AUTH_FAILURE_REDIRECT_AT) {
            handlePollingAuthFailure(result);
        }

        return null;
    }

    if (!fetchErrorShown) {
        fetchErrorShown = true;
        showAdminToast("Couldn't refresh notifications. Showing your last saved list.", { variant: 'warning' });
        showInlineError();
    }

    return null;
}

async function pollForNewNotifications() {
    const count = await refreshUnreadCount();
    if (count === null) {
        return;
    }

    if (lastUnreadCount !== null && count > lastUnreadCount) {
        await syncUnreadNotifications({ toast: true });
    } else {
        const listCount = getUnreadListItemCount();

        if (count > 0 && (listCount === 0 || listCount < count)) {
            await fetchUnreadList();
        } else if (count === 0 && listCount > 0) {
            renderNotificationList([]);
        }
    }

    lastUnreadCount = count;
}

function showInlineError() {
    const errorEl = document.querySelector('[data-notification-error]');
    if (errorEl) {
        errorEl.classList.remove('hidden');
    }
}

function hideInlineError() {
    const errorEl = document.querySelector('[data-notification-error]');
    if (errorEl) {
        errorEl.classList.add('hidden');
    }
}

function startPolling({ showDisconnectToast = false } = {}) {
    if (isPollingFallback) return;
    isPollingFallback = true;
    setConnectionState('polling', { showDisconnectToast });
}

function stopPolling() {
    isPollingFallback = false;
}

function startCountPolling() {
    if (countPollTimer) return;

    const badgeText = document.querySelector('[data-notification-badge]')?.textContent ?? '0';
    const badgeCount = badgeText === '99+' ? 99 : Number(badgeText) || 0;
    const listCount = getUnreadListItemCount();
    lastUnreadCount = Math.max(badgeCount, listCount);

    void pollForNewNotifications();
    countPollTimer = window.setInterval(() => {
        void pollForNewNotifications();
    }, COUNT_POLL_INTERVAL_MS);
}

function clearConnectTimeout() {
    if (connectTimeout) {
        window.clearTimeout(connectTimeout);
        connectTimeout = null;
    }
}

function initEcho() {
    const config = window.__pusherConfig ?? {};
    echo = createAdminEcho(config);

    if (!echo) {
        startPolling();
        return;
    }

    const userId = window.__authUserId;
    if (!userId) {
        startPolling();
        return;
    }

    setConnectionState('connecting');

    const channel = echo.private(`App.Models.User.${userId}`);

    channel.listen('.staff.notification', (payload) => {
        const item = {
            id: payload.id ?? `live-${Date.now()}`,
            type: payload.type ?? null,
            title: payload.title,
            message: payload.message,
            url: resolveNotificationUrl(payload.url),
            highlight_target: payload.highlight_target,
            created_human: payload.created_human ?? 'Just now',
            created_at: payload.created_at ?? null,
        };

        handleIncomingNotificationItem(item);
    });

    if (typeof channel.error === 'function') {
        channel.error(() => {
            startPolling();
        });
    }

    if (echo.connector?.pusher?.connection) {
        const connection = echo.connector.pusher.connection;

        connection.bind('pusher:subscription_error', () => {
            startPolling();
        });

        connection.bind('connected', () => {
            wasLiveConnected = true;
            clearConnectTimeout();
            setConnectionState('connected');
            disconnectWarningShown = false;
            stopPolling();
            void syncNotificationListWithBadge();
        });
        connection.bind('disconnected', () => {
            startPolling({ showDisconnectToast: wasLiveConnected });
        });
        connection.bind('unavailable', () => {
            startPolling({ showDisconnectToast: wasLiveConnected });
        });
        connection.bind('failed', () => {
            startPolling({ showDisconnectToast: wasLiveConnected });
        });

        connectTimeout = window.setTimeout(() => {
            if (!wasLiveConnected) {
                startPolling();
            }
        }, CONNECT_TIMEOUT_MS);
    } else {
        startPolling();
    }
}

async function markAsRead(id, link) {
    if (!routes.read) return;

    const result = await adminApi('post', routes.read.replace('__ID__', id));
    if (result.ok) {
        const row = document.querySelector(`[data-notification-id="${id}"]`);
        row?.remove();
        await refreshUnreadCount();
    } else {
        showAdminToast(result.message, { variant: 'error' });
        if (link) {
            window.location.href = link.href;
        }
    }
}

async function markAllRead() {
    if (!routes.readAll) return;

    const result = await adminApi('post', routes.readAll);
    if (result.ok) {
        renderNotificationList([]);
        updateBadge(0);
        lastUnreadCount = 0;
        document.querySelectorAll('.notification-highlight').forEach((el) => {
            el.classList.remove('notification-highlight');
        });
        showAdminToast(result.message ?? 'All notifications marked as read.', { variant: 'success' });
    } else {
        showAdminToast(result.message, { variant: 'error' });
    }
}

async function subscribePushAlerts({ prompt = false } = {}) {
    if (pushSubscribeInFlight) {
        return pushSubscribeInFlight;
    }

    pushSubscribeInFlight = subscribePushAlertsInternal({ prompt }).finally(() => {
        pushSubscribeInFlight = null;
    });

    return pushSubscribeInFlight;
}

async function subscribePushAlertsInternal({ prompt = false } = {}) {
    if (!canUseBrowserAlerts()) {
        if (prompt) {
            showAdminToast('Switching to HTTPS — browser alerts do not work on http://.', {
                variant: 'warning',
                duration: 6000,
            });
            redirectToHttpsAdmin();
        }
        updatePushButtonState();
        return false;
    }

    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        if (prompt) {
            showAdminToast("Browser alerts aren't available in this browser. In-app notifications still work.", { variant: 'warning' });
        }
        return false;
    }

    const vapidKey = window.__webPushPublicKey;
    if (!vapidKey) {
        if (prompt) {
            showAdminToast("Browser alerts aren't configured yet.", { variant: 'warning' });
        }
        return false;
    }

    try {
        let permission = Notification.permission;
        if (permission === 'default' && prompt) {
            permission = await Notification.requestPermission();
        }

        if (permission === 'denied') {
            if (prompt) {
                showAdminToast('Notifications are blocked. Allow them in your browser site settings, then click Enable browser alerts again.', {
                    variant: 'warning',
                    duration: 9000,
                });
            }
            updatePushButtonState();
            return false;
        }

        if (permission !== 'granted') {
            updatePushButtonState();
            return false;
        }

        const registration = await navigator.serviceWorker.register('/sw.js');
        await registration.update().catch(() => undefined);
        await navigator.serviceWorker.ready;
        const applicationServerKey = urlBase64ToUint8Array(vapidKey);
        let subscription = await registration.pushManager.getSubscription();

        if (subscription) {
            const existingKey = subscription.options?.applicationServerKey;
            if (existingKey && !applicationServerKeysMatch(existingKey, applicationServerKey)) {
                await subscription.unsubscribe();
                subscription = null;
            }
        }

        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey,
            });
        }

        const result = await adminApi('post', routes.pushSubscribe, subscription.toJSON());
        if (result.ok) {
            hidePushPermissionBanner();
            updatePushButtonState({ subscribed: true });
            if (prompt) {
                showAdminToast(result.message ?? 'Browser alerts enabled.', { variant: 'success' });
            }
            return true;
        }

        if (prompt) {
            showAdminToast(result.message ?? `Could not save browser subscription (${result.status || 'error'}).`, { variant: 'error' });
        }
        updatePushButtonState();
        return false;
    } catch (error) {
        console.warn('Push subscribe failed', error);
        if (prompt) {
            const message = error?.message?.includes('permission')
                ? 'Notification permission was not granted.'
                : "Browser alerts aren't available. In-app notifications still work.";
            showAdminToast(message, { variant: 'warning' });
        }
        updatePushButtonState();
        return false;
    }
}

async function fetchPushSubscriptionStatus() {
    if (!routes.pushStatus) {
        return { subscribed: false, count: 0 };
    }

    const result = await adminApi('get', routes.pushStatus);

    if (!result.ok) {
        return { subscribed: false, count: 0 };
    }

    return {
        subscribed: Boolean(result.data?.subscribed),
        count: Number(result.data?.count ?? 0),
    };
}

async function enablePushAlerts() {
    if (redirectToHttpsAdmin()) {
        return;
    }

    await subscribePushAlerts({ prompt: true });
    const status = await fetchPushSubscriptionStatus();
    updatePushButtonState({ subscribed: status.subscribed });
}

async function sendTestPushAlert() {
    if (!routes.pushTest) {
        return;
    }

    const result = await adminApi('post', routes.pushTest);

    if (result.ok) {
        const osShown = showOsNotificationFromPage({
            id: 'test-alert',
            title: 'Test order alert',
            message: 'Browser notifications are working on this device.',
            url: window.__httpsAdminDashboardUrl ?? '/admin/dashboard',
        });

        if (!osShown) {
            showAdminToast('Push sent, but no Windows popup appeared. Check Windows Focus Assist and Chrome notification settings for this site.', {
                variant: 'warning',
                duration: 10000,
            });
            return;
        }
    }

    showAdminToast(result.message ?? 'Test sent.', {
        variant: result.ok ? 'success' : 'error',
        duration: 8000,
    });
}

async function ensurePushRegistered() {
    if (!window.__webPushPublicKey) {
        updatePushButtonState();
        return;
    }

    if (!canUseBrowserAlerts()) {
        updatePushButtonState();
        if (isAdminHttp()) {
            showAdminToast('You are on http://. Browser alerts only work on https://cakeshop.test — use the popup or top button to switch.', {
                variant: 'warning',
                duration: 10000,
            });
        }
        return;
    }

    const serverStatus = await fetchPushSubscriptionStatus();
    const browserSubscribed = await isPushSubscribed();

    if (serverStatus.subscribed && browserSubscribed) {
        const registration = await navigator.serviceWorker.getRegistration('/sw.js');
        const subscription = registration ? await registration.pushManager.getSubscription() : null;
        const existingKey = subscription?.options?.applicationServerKey;
        const expectedKey = urlBase64ToUint8Array(window.__webPushPublicKey);

        if (subscription && existingKey && !applicationServerKeysMatch(existingKey, expectedKey)) {
            await subscribePushAlerts({ prompt: false });
            const status = await fetchPushSubscriptionStatus();
            updatePushButtonState({ subscribed: status.subscribed });
            return;
        }

        updatePushButtonState({ subscribed: true });
        hidePushPermissionBanner();
        return;
    }

    if (Notification.permission === 'granted') {
        const registered = await subscribePushAlerts({ prompt: false });
        const status = await fetchPushSubscriptionStatus();
        updatePushButtonState({ subscribed: status.subscribed });

        if (!status.subscribed && !hasShownPushSetupToast()) {
            showAdminToast(
                registered
                    ? 'Could not save browser registration. Click Enable browser alerts again.'
                    : 'Chrome allows notifications, but this device still needs one click on Enable browser alerts to finish setup.',
                { variant: 'warning', duration: 9000 }
            );
            markPushSetupToastShown();
        }
        return;
    }

    if (window.__promptStaffPush && Notification.permission === 'default') {
        await subscribePushAlerts({ prompt: true });
        const status = await fetchPushSubscriptionStatus();
        updatePushButtonState({ subscribed: status.subscribed });
        if (!status.subscribed) {
            showPushPermissionBanner();
        }
        return;
    }

    if (Notification.permission === 'default') {
        showPushPermissionBanner();
    }

    updatePushButtonState({ subscribed: serverStatus.subscribed && browserSubscribed });
}

function applicationServerKeysMatch(existingKey, expectedKey) {
    if (!existingKey || !expectedKey) {
        return false;
    }

    const existingBytes = existingKey instanceof ArrayBuffer
        ? new Uint8Array(existingKey)
        : new Uint8Array(existingKey);

    if (existingBytes.length !== expectedKey.length) {
        return false;
    }

    for (let i = 0; i < existingBytes.length; i += 1) {
        if (existingBytes[i] !== expectedKey[i]) {
            return false;
        }
    }

    return true;
}

function watchNotificationPermission() {
    if (!navigator.permissions?.query) {
        return;
    }

    navigator.permissions.query({ name: 'notifications' }).then((status) => {
        status.onchange = () => {
            void ensurePushRegistered();
        };
    }).catch(() => undefined);
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function initBellUi() {
    const bellButton = document.querySelector('[data-notification-bell]');
    const dropdown = document.querySelector('[data-notification-dropdown]');

    bellButton?.addEventListener('click', async () => {
        const wasHidden = dropdown?.classList.contains('hidden');
        dropdown?.classList.toggle('hidden');

        if (wasHidden && dropdown && !dropdown.classList.contains('hidden')) {
            await fetchUnreadList();
        }
    });

    document.addEventListener('click', (event) => {
        if (!dropdown || dropdown.classList.contains('hidden')) return;
        if (event.target.closest('[data-notification-bell]') || event.target.closest('[data-notification-dropdown]')) {
            return;
        }
        dropdown.classList.add('hidden');
    });

    document.querySelector('[data-notification-mark-all]')?.addEventListener('click', (e) => {
        e.preventDefault();
        markAllRead();
    });

    document.querySelector('[data-notification-retry]')?.addEventListener('click', async () => {
        hideInlineError();
        await syncNotificationListWithBadge();
    });

    document.querySelectorAll('[data-enable-push]').forEach((button) => {
        button.addEventListener('click', enablePushAlerts);
    });

    document.querySelectorAll('[data-test-push]').forEach((button) => {
        button.addEventListener('click', sendTestPushAlert);
    });

    document.querySelector('[data-push-banner-allow]')?.addEventListener('click', async () => {
        hidePushPermissionBanner();
        await subscribePushAlerts({ prompt: true });
        const status = await fetchPushSubscriptionStatus();
        updatePushButtonState({ subscribed: status.subscribed });
    });

    document.querySelector('[data-push-banner-dismiss]')?.addEventListener('click', () => {
        localStorage.setItem(PUSH_BANNER_DISMISS_KEY, '1');
        hidePushPermissionBanner();
    });

    document.querySelector('[data-notification-list]')?.addEventListener('click', async (event) => {
        const link = event.target.closest('[data-notification-link]');
        if (!link) return;
        const row = link.closest('[data-notification-id]');
        const id = row?.getAttribute('data-notification-id');
        if (!id) return;
        event.preventDefault();
        await markAsRead(id, link);
        window.location.href = link.getAttribute('href');
    });
}

function initServiceWorkerMessages() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    navigator.serviceWorker.addEventListener('message', (event) => {
        if (event.data?.type !== 'staff-notification') {
            return;
        }

        const payload = event.data.payload ?? {};
        handleIncomingNotificationItem({
            id: payload.id ?? payload.data?.id ?? `push-${Date.now()}`,
            type: payload.type ?? payload.data?.type ?? null,
            title: payload.title ?? 'Notification',
            message: payload.body ?? payload.message ?? '',
            url: resolveNotificationUrl(payload.data?.url ?? payload.url ?? '#'),
            highlight_target: payload.highlight_target ?? null,
            created_at: new Date().toISOString(),
            created_human: 'Just now',
        });
    });
}

function handleIncomingNotificationItem(item) {
    if (!item) {
        return;
    }

    prependNotificationItem(item);

    toastNotificationItem(item, { force: true });

    if (!isTabVisible()) {
        showOsNotificationFromPage(item);
    }

    refreshUnreadCount();
}

document.addEventListener('DOMContentLoaded', () => {
    if (!document.querySelector('[data-notification-bell]')) {
        return;
    }

    const targets = window.__unreadHighlightTargets ?? [];
    applyHighlightTargets(targets);

    pageLoadMs = Date.now();
    seedSeenNotificationIds();

    initBellUi();
    initEcho();
    startCountPolling();
    void syncNotificationListWithBadge();
    initServiceWorkerMessages();
    watchNotificationPermission();

    if (window.__promptStaffPush) {
        clearPushSetupToastShown();
    }

    void ensurePushRegistered();

    document.addEventListener('visibilitychange', () => {
        if (isTabVisible()) {
            unlockNotificationSound();
            void syncNotificationListWithBadge();
            void syncUnreadNotifications({ toast: true });
        }
    });

    document.addEventListener('click', () => unlockNotificationSound(), { once: true });
    document.addEventListener('keydown', () => unlockNotificationSound(), { once: true });
});

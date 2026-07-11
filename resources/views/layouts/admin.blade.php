<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="admin-area">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @php
            $adminNeedsHttps = ! request()->secure() && preg_match('/\.(test|localhost)$/i', request()->getHost());
            $adminHttpsUrl = $adminNeedsHttps
                ? 'https://'.request()->getHost().request()->getRequestUri()
                : null;
        @endphp
        @if($adminNeedsHttps && $adminHttpsUrl)
        <meta http-equiv="refresh" content="0;url={{ $adminHttpsUrl }}">
        <script>location.replace(@json($adminHttpsUrl));</script>
        @endif

        <title>@yield('title', config('app.name', 'Laravel')) – Admin</title>
        @if(header_icon_url())
        <link rel="icon" href="{{ header_icon_url() }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @stack('styles')
        @vite(['resources/css/app.css', 'resources/js/admin.js'])
        <script>
            window.__pusherConfig = @json($pusherFrontendConfig ?? ['enabled' => false, 'key' => null, 'cluster' => 'mt1']);
            window.__authUserId = @json(auth()->id());
            window.__isSecureContext = @json(request()->secure());
            window.__httpsAdminDashboardUrl = @json(preg_replace('#^http:#i', 'https:', route('admin.dashboard')));
            window.__webPushPublicKey = @json(($webPushEnabled ?? false) ? \App\Models\Setting::getWebPushPublicKey() : null);
            window.__unreadHighlightTargets = @json(($unreadHighlightTargets ?? collect())->values());
            window.__promptStaffPush = @json(($webPushEnabled ?? false) && session()->pull('prompt_staff_push', false));
            window.__notificationRoutes = {
                index: @json(route('admin.notifications.index')),
                unreadCount: @json(route('admin.notifications.unread-count')),
                read: @json(route('admin.notifications.read', ['id' => '__ID__'])),
                readAll: @json(route('admin.notifications.read-all')),
                pushSubscribe: @json(route('admin.push-subscriptions.store')),
                pushStatus: @json(route('admin.push-subscriptions.status')),
                pushTest: @json(route('admin.push-subscriptions.test')),
                orderSoundUrl: @json(asset('sounds/order_notification.mp3')),
            };
        </script>
        <x-app-messages-script />
    </head>
    <body class="bg-gray-50 font-sans antialiased text-gray-900">
        @if($adminNeedsHttps ?? false)
        <div class="fixed inset-0 z-[20000] flex items-center justify-center bg-gray-900/70 p-4">
            <div class="w-full max-w-lg rounded-2xl border border-amber-200 bg-white p-6 shadow-2xl">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('HTTPS required for browser alerts') }}</h2>
                <p class="mt-2 text-sm text-gray-600">
                    {{ __('You opened the admin on http://. Chrome cannot register push notifications on HTTP. Permission you enabled on http:// does not apply to https://.') }}
                </p>
                <a
                    href="{{ $adminHttpsUrl }}"
                    class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"
                >
                    {{ __('Open secure admin (HTTPS)') }}
                </a>
                <p class="mt-3 text-xs text-gray-500">{{ __('After switching, click Allow browser notifications again on the Settings page.') }}</p>
            </div>
        </div>
        @endif
        @if($webPushEnabled ?? false)
        <div
            data-push-permission-banner
            class="fixed inset-0 z-[10000] hidden items-center justify-center bg-gray-900/50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="push-permission-title"
        >
            <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                <h2 id="push-permission-title" class="text-lg font-semibold text-gray-900">{{ __('Allow order notifications?') }}</h2>
                <p class="mt-2 text-sm text-gray-600">
                    {{ __('Get browser popups when new orders arrive — even when this tab is closed or minimized. You stay logged in on this device until you sign out.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button
                        type="button"
                        data-push-banner-allow
                        class="inline-flex flex-1 items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                    >
                        {{ __('Allow notifications') }}
                    </button>
                    <button
                        type="button"
                        data-push-banner-dismiss
                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        {{ __('Not now') }}
                    </button>
                </div>
            </div>
        </div>
        @endif
        <div id="admin-toast-container" class="pointer-events-none fixed bottom-6 right-4 z-[9999] flex w-full max-w-sm flex-col items-end gap-3 sm:right-6" aria-live="polite"></div>
        <div class="min-h-screen">
            <div
                class="admin-sidebar-backdrop fixed inset-0 z-40 bg-gray-900/50"
                data-admin-sidebar-backdrop
                aria-hidden="true"
                hidden
            ></div>

            @include('admin.partials._sidebar')

            <main class="admin-main py-8 px-4 sm:px-6 lg:px-8">
                <x-admin-topbar />
                <x-flash-messages show-form-errors />
                @yield('content')
            </main>
        </div>
        @stack('scripts')
    </body>
</html>

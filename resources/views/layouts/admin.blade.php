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
            <!-- Sidebar: fixed header + footer, scrollable nav -->
            <aside class="admin-sidebar shrink-0 border-r border-gray-200 bg-white">
                <div class="shrink-0 border-b border-gray-100 bg-gray-50/50 px-5 py-5">
                    <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold tracking-tight text-gray-900">{{ config('app.name') }}</a>
                    <p class="mt-0.5 text-xs font-medium uppercase tracking-wider text-gray-400">Admin</p>
                </div>
                <nav class="admin-sidebar__nav space-y-0.5 px-3 py-4">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        Dashboard
                    </a>
                    @can('categories.view')
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        Categories
                    </a>
                    @endcan
                    @can('products.view')
                    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.products.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        Products
                    </a>
                    @endcan
                    @can('flavors.view')
                    <a href="{{ route('admin.flavors.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.flavors.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                        Flavors
                    </a>
                    @endcan
                    @can('orders.view')
                    @role('Admin')
                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.orders.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        Orders
                    </a>
                    @endrole
                    <a href="{{ route('admin.kitchen.orders.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.kitchen.orders.index') || request()->routeIs('admin.kitchen.orders.show') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Today's orders
                    </a>
                    <a href="{{ route('admin.kitchen.orders.upcoming') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.kitchen.orders.upcoming*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Upcoming orders
                    </a>
                    @endcan
                    @can('customers.view')
                    <a href="{{ route('admin.customers.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.customers.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        Customers
                    </a>
                    @endcan
                    @can('users.view')
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        Users
                    </a>
                    @endcan
                    @can('features.view')
                    <a href="{{ route('admin.features.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.features.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Features
                    </a>
                    @endcan
                    @can('sliders.view')
                    <a href="{{ route('admin.sliders.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.sliders.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        {{ __('Sliders') }}
                    </a>
                    @endcan
                    @can('testimonials.view')
                    <a href="{{ route('admin.testimonials.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.testimonials.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        Testimonials
                    </a>
                    @endcan
                    @can('settings.manage')
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Settings
                    </a>
                    @endcan
                    @can('contact_enquiries.view')
                    <a href="{{ route('admin.contact-enquiries.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs('admin.contact-enquiries.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        Contact Enquiries
                    </a>
                    @endcan
                </nav>
                <div class="shrink-0 border-t border-gray-100 bg-white px-3 py-4 space-y-1">
                    <a href="{{ route('admin.profile.edit') }}" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-600 transition duration-200 hover:bg-gray-50 hover:text-gray-900">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-600 transition duration-200 hover:bg-gray-50 hover:text-gray-900">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            Log out
                        </button>
                    </form>
                </div>
            </aside>

            <main class="admin-main py-8 px-4 sm:px-6 lg:px-8">
                <x-admin-topbar />
                <x-flash-messages show-form-errors />
                @yield('content')
            </main>
        </div>
        @stack('scripts')
    </body>
</html>

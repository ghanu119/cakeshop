<header class="admin-topbar sticky top-0 z-30 -mx-4 mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-white/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="flex min-w-0 flex-1 items-center gap-3">
        <button
            type="button"
            class="lg:hidden inline-flex shrink-0 items-center justify-center rounded-lg p-2.5 text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
            data-admin-sidebar-toggle
            aria-controls="admin-sidebar"
            aria-expanded="false"
            aria-label="{{ __('Open menu') }}"
        >
            <svg class="h-6 w-6" data-admin-sidebar-icon-open fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg class="hidden h-6 w-6" data-admin-sidebar-icon-close fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">{{ config('app.name') }}</p>
            <h1 class="truncate text-lg font-semibold text-gray-900">@yield('title', __('Admin'))</h1>
        </div>
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        @if(($webPushEnabled ?? false))
            <button
                type="button"
                data-test-push
                class="hidden inline-flex rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                title="{{ __('Send a test popup to this browser') }}"
            >
                {{ __('Test alert') }}
            </button>
            <button
                type="button"
                data-enable-push
                class="inline-flex rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 shadow-sm transition hover:bg-indigo-100"
                title="{{ __('Receive system notifications for new orders (requires HTTPS and staying logged in on this device)') }}"
            >
                <span data-enable-push-label>{{ __('Enable browser alerts') }}</span>
            </button>
        @endif

        @if(($notificationsEnabled ?? true) && !($notificationSystemUnavailable ?? false))
            <x-notification-bell />
        @elseif($notificationSystemUnavailable ?? false)
            <x-notification-bell />
        @endif
    </div>
</header>

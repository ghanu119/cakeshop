<header class="admin-topbar sticky top-0 z-30 -mx-4 mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-white/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="min-w-0">
        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">{{ config('app.name') }}</p>
        <h1 class="truncate text-lg font-semibold text-gray-900">@yield('title', __('Admin'))</h1>
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

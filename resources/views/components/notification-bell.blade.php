@php
    $count = $unreadNotificationCount ?? 0;
    $items = $unreadNotifications ?? collect();
    $unavailable = $notificationSystemUnavailable ?? false;
@endphp

<div class="relative" data-notification-root>
    <button
        type="button"
        data-notification-bell
        class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-900"
        aria-label="{{ __('Notifications') }}"
    >
        <span
            data-notification-connection
            class="absolute right-1 top-1 h-2 w-2 rounded-full bg-gray-300"
            title="{{ __('Notifications') }}"
        ></span>
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span
            data-notification-badge
            class="{{ $count > 0 ? '' : 'hidden' }} absolute -right-1 -top-1 inline-flex min-h-[1.125rem] min-w-[1.125rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
        >{{ $count > 99 ? '99+' : $count }}</span>
    </button>

    <div
        data-notification-dropdown
        class="notification-bell-dropdown absolute right-0 z-50 mt-2 hidden w-80 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg ring-1 ring-black/5"
    >
        <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-900">{{ __('Notifications') }}</h3>
            <button type="button" data-notification-mark-all class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                {{ __('Mark all read') }}
            </button>
        </div>

        @if($unavailable)
            <div class="border-b border-amber-100 bg-amber-50 px-4 py-3" data-notification-error>
                <p class="text-sm text-amber-800">{{ __('Notifications temporarily unavailable.') }}</p>
                <button type="button" data-notification-retry class="mt-2 text-xs font-semibold text-amber-900 underline">
                    {{ __('Retry') }}
                </button>
            </div>
        @else
            <div class="hidden border-b border-amber-100 bg-amber-50 px-4 py-3" data-notification-error>
                <p class="text-sm text-amber-800">{{ __("Couldn't refresh notifications. Showing your last saved list.") }}</p>
                <button type="button" data-notification-retry class="mt-2 text-xs font-semibold text-amber-900 underline">
                    {{ __('Retry') }}
                </button>
            </div>
        @endif

        <ul class="max-h-80 overflow-y-auto" data-notification-list>
            @forelse($items as $notification)
                @php $data = $notification->data; @endphp
                <li data-notification-id="{{ $notification->id }}">
                    <a href="{{ \App\Support\StaffNotificationUrl::toAppPath($data['url'] ?? null) }}" class="block px-4 py-3 hover:bg-gray-50" data-notification-link>
                        <p class="text-sm font-semibold text-gray-900">{{ $data['title'] ?? '' }}</p>
                        <p class="mt-0.5 text-xs text-gray-600">{{ $data['message'] ?? '' }}</p>
                        <p class="mt-1 text-[10px] text-gray-400">
                            <time datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->diffForHumans() }}</time>
                        </p>
                    </a>
                </li>
            @empty
                <li data-notification-empty class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No unread notifications') }}</li>
            @endforelse
        </ul>
    </div>
</div>

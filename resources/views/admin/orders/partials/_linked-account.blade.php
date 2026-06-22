@php
    $order->loadMissing('user');
    $collapseLinkedAccount = $order->hasDistinctContactFromAccount();
    $linkedAccountPanelId = 'linked-account-'.$order->id;
@endphp

@if($order->user)
    <div class="mb-8 border-t border-gray-100 pt-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ __('Linked account') }}</h4>
            @if($collapseLinkedAccount)
                <button
                    type="button"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    aria-expanded="false"
                    aria-controls="{{ $linkedAccountPanelId }}"
                    data-linked-account-toggle
                    data-panel-id="{{ $linkedAccountPanelId }}"
                    data-show-label="{{ __('Reveal detail') }}"
                    data-hide-label="{{ __('Hide detail') }}"
                >{{ __('Reveal detail') }}</button>
            @endif
        </div>
        <div
            id="{{ $linkedAccountPanelId }}"
            @class(['grid grid-cols-1 gap-4 sm:grid-cols-3', 'hidden' => $collapseLinkedAccount])
        >
            <div>
                <p class="mb-1 text-sm font-medium text-gray-500">{{ __('Name') }}</p>
                <p class="font-semibold text-gray-900">
                    <a href="{{ route('admin.customers.show', $order->user) }}" class="text-indigo-600 hover:text-indigo-800">{{ $order->user->name }}</a>
                </p>
            </div>
            <div>
                <p class="mb-1 text-sm font-medium text-gray-500">{{ __('Phone') }}</p>
                <p class="font-semibold text-gray-900">{{ $order->user->phone ?? '—' }}</p>
            </div>
            <div>
                <p class="mb-1 text-sm font-medium text-gray-500">{{ __('Email') }}</p>
                <p class="break-all font-semibold text-gray-900">{{ $order->user->email ?? '—' }}</p>
            </div>
        </div>
    </div>
@endif

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-linked-account-toggle]').forEach(function(button) {
                button.addEventListener('click', function() {
                    var panel = document.getElementById(button.getAttribute('data-panel-id'));
                    if (!panel) {
                        return;
                    }

                    var isHidden = panel.classList.toggle('hidden');
                    button.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
                    button.textContent = isHidden
                        ? button.getAttribute('data-show-label')
                        : button.getAttribute('data-hide-label');
                });
            });
        </script>
    @endpush
@endonce

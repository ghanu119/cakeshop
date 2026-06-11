@props([
    'message' => null,
])

@php
    $text = $message ?? $errors->first('_form');
@endphp

@if ($text)
    <div
        data-form-system-error
        role="alert"
        {{ $attributes->merge(['class' => 'mb-6 rounded-2xl border-2 border-red-400 bg-red-50 p-4 shadow-md ring-1 ring-red-200 sm:p-5']) }}
    >
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-base font-semibold leading-snug text-red-900">{{ $text }}</p>
                <button
                    type="button"
                    onclick="window.location.reload()"
                    class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
                >
                    {{ __('Refresh page') }}
                </button>
            </div>
        </div>
    </div>
    <script>
        document.querySelector('[data-form-system-error]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    </script>
@endif

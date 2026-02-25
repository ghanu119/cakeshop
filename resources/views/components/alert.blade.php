@props([
    'variant' => 'info',
    'dismissible' => false,
])

@php
    $variants = [
        'success' => 'bg-green-50 text-green-800 border-green-200',
        'error' => 'bg-red-50 text-red-800 border-red-200',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
        'info' => 'bg-blue-50 text-blue-800 border-blue-200',
    ];
    $class = 'rounded-lg border p-4 text-sm ' . ($variants[$variant] ?? $variants['info']);
@endphp

<div
    role="alert"
    {{ $attributes->merge(['class' => $class]) }}
    x-data="{ show: true }"
    x-show="show"
    x-transition
>
    <div class="flex items-start gap-3">
        <span class="flex-1">{{ $slot }}</span>
        @if ($dismissible)
            <button
                type="button"
                class="shrink-0 rounded p-1 hover:bg-black/5 focus:outline-none focus:ring-2 focus:ring-offset-2"
                aria-label="Dismiss"
                @click="show = false"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </button>
        @endif
    </div>
</div>

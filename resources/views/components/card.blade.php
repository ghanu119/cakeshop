@props([
    'padding' => true,
    'elevated' => false,
])

<div
    {{ $attributes->merge([
        'class' => 'rounded-xl border border-gray-200 bg-white ' . ($elevated ? 'shadow-md' : 'shadow-sm') . ($padding ? ' p-6 sm:p-8' : ''),
    ]) }}
>
    {{ $slot }}
</div>

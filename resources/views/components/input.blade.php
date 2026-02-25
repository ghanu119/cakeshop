@props([
    'disabled' => false,
])

<input
    @disabled($disabled)
    {!! $attributes->merge([
        'class' => 'rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 transition duration-200 placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:ring-offset-0 disabled:opacity-50',
    ]) !!}
>

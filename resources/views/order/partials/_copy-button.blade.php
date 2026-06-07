@props([
    'text',
    'label',
    'toast' => null,
])

<button
    type="button"
    {{ $attributes->merge([
        'class' => 'inline-flex min-h-[2.5rem] shrink-0 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-full border border-stone-200 bg-white px-5 py-2.5 text-xs font-semibold leading-none text-stone-600 shadow-sm transition-[color,background-color,border-color,box-shadow] duration-150 hover:border-stone-300 hover:bg-stone-50 hover:text-stone-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 aria-[copied=true]:border-green-400 aria-[copied=true]:bg-green-50 aria-[copied=true]:text-green-700',
    ]) }}
    data-copy-text="{{ $text }}"
    data-copy-label="{{ $label }}"
    @if($toast) data-copy-toast="{{ $toast }}" @endif
    aria-label="{{ $label }}"
    aria-copied="false"
>
    <svg class="h-3.5 w-3.5 shrink-0 copy-btn-icon" data-copy-icon="default" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
    </svg>
    <svg class="hidden h-3.5 w-3.5 shrink-0 copy-btn-icon" data-copy-icon="success" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
    </svg>
    <span data-copy-label-text class="px-0.5">{{ $label }}</span>
</button>

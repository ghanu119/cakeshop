@props([
    'column',
    'label' => null,
    'defaultSort' => 'name_en',
    'defaultDirection' => 'asc',
])

@php
    $currentSort = request('sort', $defaultSort);
    $currentDirection = request('direction', $defaultDirection) === 'desc' ? 'desc' : 'asc';
    $isActive = $currentSort === $column;
    $nextDirection = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery([
        'sort' => $column,
        'direction' => $nextDirection,
        'page' => 1,
    ]);
    $text = $label ?? $slot;
@endphp

<th {{ $attributes->merge(['class' => 'px-5 py-3.5 text-left text-sm font-medium text-gray-600']) }}>
    <a
        href="{{ $url }}"
        class="group inline-flex items-center gap-1 transition duration-150 hover:text-gray-900 {{ $isActive ? 'text-gray-900' : '' }}"
    >
        <span>{{ $text }}</span>
        <span class="inline-flex text-gray-400 group-hover:text-gray-600 {{ $isActive ? 'text-gray-700' : '' }}">
            @if($isActive)
                @if($currentDirection === 'asc')
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                @else
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                @endif
            @else
                <svg class="h-4 w-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
            @endif
        </span>
    </a>
</th>

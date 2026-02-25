@props([])

<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
    <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200']) }}>
        {{ $slot }}
    </table>
</div>

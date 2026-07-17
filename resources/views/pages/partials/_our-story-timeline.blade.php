@php
    $milestones = [
        [
            'icon' => 'gift',
            'title' => __('August 2015'),
            'text' => __('Better Buns Live Bakery began its journey in Rajkot with a vision to serve fresh, delicious and dependable bakery products every day.'),
        ],
        [
            'icon' => 'sparkles',
            'title' => __('Everyday bakery essentials'),
            'text' => __('Our range grew to include sandwich bread, pav bhaji buns, pizza bases, toast, cake rusk, pastries, puffs and different varieties of khari.'),
        ],
        [
            'icon' => 'award',
            'title' => __('Celebration cakes'),
            'text' => __('We became part of birthdays, anniversaries and special occasions with cakes and customized cake designs made to match each celebration.'),
        ],
        [
            'icon' => 'bulb',
            'title' => __('Trusted by families'),
            'text' => __('We continue to serve our customers with freshness, consistent taste, hygienic preparation and dependable service for every age and occasion.'),
        ],
    ];
@endphp

<div
    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8"
    data-testid="our-story-timeline"
>
    @foreach ($milestones as $milestone)
        @php
            $step = str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT);
        @endphp
        <article
            class="flex flex-col items-center rounded-3xl border border-stone-100 bg-white p-8 text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_40px_rgb(217,119,6,0.12)] hover:border-amber-100"
            data-aos="fade-up"
            data-aos-delay="{{ $loop->index * 100 }}"
        >
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                @if ($milestone['icon'] === 'gift')
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                @elseif ($milestone['icon'] === 'sparkles')
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                @elseif ($milestone['icon'] === 'award')
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                @else
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                @endif
            </div>
            <p class="mb-2 text-xs font-bold uppercase tracking-widest text-amber-600">{{ $step }}</p>
            <h3 class="font-display text-xl font-bold text-stone-900 mb-3">{{ $milestone['title'] }}</h3>
            <p class="text-stone-500 text-sm leading-relaxed">{{ $milestone['text'] }}</p>
        </article>
    @endforeach
</div>

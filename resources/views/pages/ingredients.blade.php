@extends('layouts.app')

@section('title', __('Ingredients') . ' - ' . (settings('site_name') ?: config('app.name')))

@section('content')
@php
    $siteName = settings('site_name') ?: config('app.name');
    $ingredientCategories = [
        [
            'title' => __('Cake bases'),
            'text' => __('Soft sponge layers baked fresh daily—vanilla bean, rich cocoa, and seasonal fruit infusions.'),
            'tag' => __('Sponge & layers'),
            'span' => 'md:col-span-2 md:row-span-2',
            'icon' => 'layers',
        ],
        [
            'title' => __('Buttercream & frostings'),
            'text' => __('Silky Swiss meringue, whipped cream cheese, and ganache finishes—balanced sweetness, never cloying.'),
            'tag' => __('Finish'),
            'span' => '',
            'icon' => 'frosting',
        ],
        [
            'title' => __('Fillings & layers'),
            'text' => __('Fruit compotes, caramel ribbons, nut pralines, and chocolate mousses between every tier.'),
            'tag' => __('Inside every slice'),
            'span' => '',
            'icon' => 'filling',
        ],
        [
            'title' => __('Chocolate & cocoa'),
            'text' => __('Belgian couverture and dutched cocoa for depth—melt-in-mouth, never waxy.'),
            'tag' => __('Premium cocoa'),
            'span' => '',
            'icon' => 'cocoa',
        ],
        [
            'title' => __('Fresh fruit & zest'),
            'text' => __('Seasonal berries, citrus zest, and purees folded in at peak ripeness.'),
            'tag' => __('Natural flavor'),
            'span' => 'md:col-span-2',
            'icon' => 'fruit',
        ],
        [
            'title' => __('Nuts, spices & accents'),
            'text' => __('Toasted almonds, pistachio crumble, cardamom, and vanilla—measured for aroma, not overwhelm.'),
            'tag' => __('Detail'),
            'span' => '',
            'icon' => 'spice',
        ],
    ];
    $standards = [
        ['label' => __('No artificial colors'), 'detail' => __('Natural tints from fruit, cocoa, and caramel only.')],
        ['label' => __('Fresh daily bake'), 'detail' => __('Cakes prepared to order—never pulled from a freezer case.')],
        ['label' => __('Allergen transparency'), 'detail' => __('Ask us about nuts, dairy, gluten, and eggs before you order.')],
        ['label' => __('Small-batch craft'), 'detail' => __('Every batch is tasted and adjusted by our pastry team.')],
    ];
@endphp

{{-- Breadcrumb --}}
<section class="border-b border-stone-100 bg-white py-4" data-testid="ingredients-page">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <nav class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-stone-500" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('home') }}" class="transition-colors hover:text-amber-600">{{ __('Home') }}</a>
            <svg class="h-4 w-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="text-stone-900">{{ __('Ingredients') }}</span>
        </nav>
    </div>
</section>

{{-- Hero --}}
<section class="ingredients-hero relative overflow-hidden py-20 lg:py-28">
    <div class="pointer-events-none absolute inset-0 z-0" aria-hidden="true">
        <div class="absolute -right-20 top-0 h-80 w-80 rounded-full bg-amber-200/30 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/4 h-64 w-64 rounded-full bg-orange-200/25 blur-3xl"></div>
    </div>
    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <span class="mb-6 inline-block rounded-full border border-amber-200 bg-amber-100 px-4 py-1.5 text-sm font-bold tracking-wider text-amber-800 shadow-sm">{{ __('Inside every cake') }}</span>
                <h1 class="font-display text-4xl font-extrabold leading-tight tracking-tight text-stone-900 sm:text-5xl lg:text-6xl">
                    {{ __('Ingredients') }} <span class="text-amber-600">{{ __('you can trust') }}</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg font-light leading-relaxed text-stone-600 sm:text-xl">
                    {{ __('At :name, we bake with real butter, fresh eggs, and ingredients chosen for flavor—not fillers. Here is what goes into our cakes, layer by layer.', ['name' => $siteName]) }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-3.5 text-base font-semibold text-white shadow-lg transition-all duration-200 hover:from-amber-600 hover:to-orange-600 hover:shadow-xl">
                        {{ __('Browse our cakes') }}
                    </a>
                    <a href="{{ route('contact.index') }}" class="inline-flex items-center justify-center rounded-xl border-2 border-amber-200 bg-white px-6 py-3.5 text-base font-semibold text-stone-700 shadow-sm transition-all duration-200 hover:border-amber-400 hover:text-amber-700">
                        {{ __('Ask about allergens') }}
                    </a>
                </div>
            </div>
            <div class="ingredients-hero-card relative rounded-3xl border border-amber-200/60 bg-white/80 p-8 shadow-[var(--theme-card-shadow)] backdrop-blur-sm lg:p-10">
                <p class="text-sm font-semibold uppercase tracking-wider text-amber-700">{{ __('Our promise') }}</p>
                <ul class="mt-6 space-y-4">
                    @foreach($standards as $item)
                        <li class="flex gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100 text-amber-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-stone-900">{{ $item['label'] }}</p>
                                <p class="mt-0.5 text-sm leading-relaxed text-stone-600">{{ $item['detail'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Bento: ingredient categories --}}
<section class="section-warm py-20 lg:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-14 text-center">
            <span class="mb-4 inline-block rounded-full bg-orange-100 px-3 py-1 text-sm font-semibold text-orange-800">{{ __('What we use') }}</span>
            <h2 class="font-display text-3xl font-bold text-stone-900 sm:text-4xl">{{ __('From sponge to finishing touch') }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-lg font-light text-stone-500">{{ __('Every element is chosen to complement flavor, texture, and the design on top.') }}</p>
        </div>

        <div class="ingredients-bento grid grid-cols-1 gap-4 md:grid-cols-4 md:grid-rows-2 md:gap-5" data-testid="ingredients-bento">
            @foreach($ingredientCategories as $card)
                <article class="ingredients-bento-card group relative overflow-hidden rounded-3xl border border-stone-100 bg-white p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-[var(--theme-card-shadow-hover)] {{ $card['span'] }}">
                    <div class="absolute left-0 top-0 h-1 w-full origin-left scale-x-0 bg-gradient-to-r from-amber-400 to-orange-500 transition-transform duration-300 group-hover:scale-x-100"></div>
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 transition-colors duration-300 group-hover:bg-gradient-to-br group-hover:from-amber-500 group-hover:to-orange-500 group-hover:text-white">
                            @if($card['icon'] === 'layers')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            @elseif($card['icon'] === 'frosting')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707" /></svg>
                            @elseif($card['icon'] === 'cocoa')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            @else
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                            @endif
                        </div>
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">{{ $card['tag'] }}</span>
                    </div>
                    <h3 class="text-xl font-bold text-stone-900">{{ $card['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600 {{ $card['span'] ? 'sm:text-base' : '' }}">{{ $card['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Process strip --}}
<section class="border-y border-amber-100/80 bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-8 sm:grid-cols-3 sm:gap-6">
            @foreach([
                ['step' => '01', 'title' => __('Source'), 'text' => __('Trusted suppliers for dairy, flour, and chocolate—we inspect every delivery.')],
                ['step' => '02', 'title' => __('Craft'), 'text' => __('Measured recipes, rested batters, and cooled layers before assembly.')],
                ['step' => '03', 'title' => __('Decorate'), 'text' => __('Design finishes use the same quality—edible prints, florals, and hand-piped details.')],
            ] as $step)
                <div class="relative text-center sm:text-left">
                    <span class="font-display text-5xl font-extrabold text-amber-200/90">{{ $step['step'] }}</span>
                    <h3 class="mt-2 text-lg font-bold text-stone-900">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Per-cake highlights from catalog --}}
@if($productsWithIngredients->isNotEmpty())
<section class="py-20 lg:py-28 bg-stone-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-12 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="font-display text-3xl font-bold text-stone-900 sm:text-4xl">{{ __('In our cakes') }}</h2>
                <p class="mt-2 max-w-xl text-stone-600">{{ __('Ingredient highlights from our menu—each cake lists what makes it special.') }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex shrink-0 items-center gap-2 text-sm font-semibold text-amber-700 transition-colors hover:text-amber-800">
                {{ __('View full menu') }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-testid="ingredients-product-list">
            @foreach($productsWithIngredients as $product)
                @php
                    $items = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $product->ingredients)));
                @endphp
                <article class="flex flex-col overflow-hidden rounded-2xl border border-stone-100 bg-white shadow-sm transition-shadow duration-200 hover:shadow-[var(--theme-card-shadow)]">
                    <div class="border-b border-amber-100/80 bg-gradient-to-br from-amber-50 to-orange-50/80 px-6 py-5">
                        <h3 class="text-lg font-bold text-stone-900">
                            <a href="{{ route('products.show', $product->slug) }}" class="transition-colors hover:text-amber-700">{{ $product->name_en }}</a>
                        </h3>
                        @if($product->category)
                            <p class="mt-1 text-sm font-medium text-amber-700">{{ $product->category->name_en }}</p>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col px-6 py-5">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-stone-500">{{ __('Key ingredients') }}</p>
                        <ul class="flex flex-wrap gap-2">
                            @foreach($items as $item)
                                <li>
                                    <span class="inline-flex rounded-full border border-amber-200/80 bg-amber-50 px-3 py-1 text-sm font-medium text-stone-800">{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('products.show', $product->slug) }}" class="mt-5 inline-flex items-center text-sm font-semibold text-amber-700 hover:text-amber-800">
                            {{ __('See cake details') }}
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- CTA --}}
<section class="relative overflow-hidden py-20 lg:py-24">
    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 via-orange-50 to-amber-100/40" aria-hidden="true"></div>
    <div class="relative z-10 mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="font-display text-3xl font-bold text-stone-900 sm:text-4xl">{{ __('Ready to taste the difference?') }}</h2>
        <p class="mt-4 text-lg text-stone-600">{{ __('Order a cake made with the same care we put into every ingredient.') }}</p>
        <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ route('products.index') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-8 py-4 text-lg font-semibold text-white shadow-lg transition-all duration-200 hover:from-amber-600 hover:to-orange-600 sm:w-auto">
                {{ __('Order a cake') }}
            </a>
            <a href="{{ route('about') }}" class="inline-flex w-full items-center justify-center rounded-xl border-2 border-amber-200 bg-white px-8 py-4 text-lg font-semibold text-stone-700 transition-all duration-200 hover:border-amber-400 sm:w-auto">
                {{ __('Our story') }}
            </a>
        </div>
    </div>
</section>
@endsection

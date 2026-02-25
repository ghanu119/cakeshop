@extends('layouts.app')

@section('content')
{{-- Hero: New Release, Sweetness Redefined, Shop Now / Learn More --}}
<section class="hero-lumiere relative flex items-center overflow-hidden min-h-[85vh]">
    <div class="relative z-10 w-full mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div class="hero-content">
                <p class="hero-label text-xs uppercase tracking-[0.2em] mb-2">{{ __('New Release') }}</p>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl mb-4">
                    <span class="hero-title-part1">{{ __('Sweetness') }}</span>
                    <span class="hero-title-part2"> {{ __('Redefined') }}</span>
                </h1>
                <p class="hero-desc text-lg text-stone-600 mb-8 max-w-lg leading-relaxed">
                    {{ __('Experience the art of French patisserie and handcrafted cakes made with intention and the finest ingredients.') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('products.index') }}" class="btn-hero-primary inline-flex items-center justify-center px-8 py-4 font-semibold text-lg rounded-xl">{{ __('Shop Now') }}
                        <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                    <a href="{{ route('about') }}" class="btn-hero-outline inline-flex items-center justify-center px-8 py-4 font-semibold text-lg rounded-xl">{{ __('Learn More') }}</a>
                </div>
            </div>
            <div class="hidden lg:block">
                <div class="aspect-square rounded-2xl overflow-hidden bg-stone-200/60 flex items-center justify-center">
                    <svg class="w-3/4 h-3/4 text-stone-400/50" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="100" cy="155" rx="70" ry="12" fill="currentColor" opacity="0.2"/>
                        <rect x="55" y="95" width="90" height="55" rx="8" fill="currentColor" opacity="0.25"/>
                        <rect x="60" y="65" width="80" height="35" rx="6" fill="currentColor" opacity="0.35"/>
                        <rect x="70" y="40" width="60" height="30" rx="4" fill="currentColor" opacity="0.4"/>
                        <circle cx="100" cy="55" r="8" fill="currentColor" opacity="0.5"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Sweet Delights banner: orange-brown gradient --}}
<section class="lumiere-sweet-delights py-20 lg:py-28">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl sm:text-5xl font-serif font-normal text-white mb-4" style="font-family: 'Cormorant Garamond', Georgia, serif;">{{ __('Sweet Delights') }}</h2>
        <p class="text-lg text-white/95 mb-8 max-w-2xl mx-auto leading-relaxed">
            {{ __('From classic layer cakes to delicate pastries, each creation is crafted with care and the finest ingredients. Discover your next favorite treat.') }}
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-[#5A5A40] font-semibold text-lg rounded-xl border-2 border-[#5A5A40] hover:bg-stone-50 transition-colors">
                {{ __('Shop Now') }}
            </a>
            <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center px-8 py-4 bg-transparent text-white font-semibold text-lg rounded-xl border-2 border-white hover:bg-white hover:text-[#5A5A40] transition-colors">
                {{ __('Explore') }}
            </a>
        </div>
    </div>
</section>

{{-- Crafted with Intention: gold circular icons --}}
<section class="py-20 lg:py-28 bg-[#f5f5f0]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="heading-display text-4xl sm:text-5xl mb-4">{{ __('Crafted with Intention') }}</h2>
            <p class="text-xl text-stone-600 max-w-2xl mx-auto">{{ __('Organic ingredients, artisanal process, premium recipes—every detail matters.') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @forelse($features->take(4) as $feature)
                <div class="lumiere-feature-card card-modern p-8 text-center">
                    <div class="lumiere-icon-gold w-16 h-16 mx-auto mb-6 rounded-full flex items-center justify-center text-white">
                        @if($feature->icon === 'shield-check')
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        @elseif($feature->icon === 'clock')
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @elseif($feature->icon === 'check-circle')
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @else
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        @endif
                    </div>
                    <h3 class="text-lg font-semibold text-stone-900 mb-2">{{ $feature->title }}</h3>
                    <p class="text-stone-600 text-sm leading-relaxed">{{ $feature->description }}</p>
                </div>
            @empty
                <div class="lumiere-feature-card card-modern p-8 text-center"><div class="lumiere-icon-gold w-16 h-16 mx-auto mb-6 rounded-full flex items-center justify-center text-white"><svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div><h3 class="text-lg font-semibold text-stone-900 mb-2">{{ __('Premium Ingredients') }}</h3><p class="text-stone-600 text-sm">{{ __('Sourced with care for the best taste') }}</p></div>
                <div class="lumiere-feature-card card-modern p-8 text-center"><div class="lumiere-icon-gold w-16 h-16 mx-auto mb-6 rounded-full flex items-center justify-center text-white"><svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div><h3 class="text-lg font-semibold text-stone-900 mb-2">{{ __('Patient Craft') }}</h3><p class="text-stone-600 text-sm">{{ __('Time-honored techniques, modern magic') }}</p></div>
                <div class="lumiere-feature-card card-modern p-8 text-center"><div class="lumiere-icon-gold w-16 h-16 mx-auto mb-6 rounded-full flex items-center justify-center text-white"><svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg></div><h3 class="text-lg font-semibold text-stone-900 mb-2">{{ __('Modern Magic') }}</h3><p class="text-stone-600 text-sm">{{ __('Innovative flavors that surprise') }}</p></div>
                <div class="lumiere-feature-card card-modern p-8 text-center"><div class="lumiere-icon-gold w-16 h-16 mx-auto mb-6 rounded-full flex items-center justify-center text-white"><svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg></div><h3 class="text-lg font-semibold text-stone-900 mb-2">{{ __('Fast Delivery') }}</h3><p class="text-stone-600 text-sm">{{ __('Fresh to your door') }}</p></div>
            @endforelse
        </div>
    </div>
</section>

{{-- Seasonal Favorites: tabs + product grid --}}
<section class="py-20 lg:py-28 bg-[#f5f5f0]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 mb-10">
            <div>
                <h2 class="heading-display text-4xl sm:text-5xl mb-2">{{ __('Seasonal Favorites') }}</h2>
                <p class="text-xl text-stone-600">{{ __('Handpicked cakes for the season') }}</p>
            </div>
            <div class="lumiere-category-tabs flex flex-wrap gap-2">
                <a href="{{ route('products.index') }}" class="lumiere-tab lumiere-tab-active px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">{{ __('All') }}</a>
                @foreach($categories->take(3) as $cat)
                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="lumiere-tab px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">{{ $cat->name_en }}</a>
                @endforeach
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
            @forelse($highlights->take(4) as $product)
                <div class="px-1">@include('products._card', ['product' => $product])</div>
            @empty
                @foreach($products->take(4) as $product)
                    <div class="px-1">@include('products._card', ['product' => $product])</div>
                @endforeach
            @endforelse
        </div>
    </div>
</section>

{{-- About Our Bakery --}}
<section class="py-20 lg:py-28 bg-[#f5f5f0]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-[#5A5A40] mb-2">{{ __('About') }}</p>
                <h2 class="heading-display text-4xl sm:text-5xl mb-6">{{ __('About Our Bakery') }}</h2>
                <p class="text-lg text-stone-600 mb-6 leading-relaxed">
                    {{ __('With years of experience in the art of baking, we bring you the finest cakes made with love and passion. Our commitment to quality and freshness ensures every bite is a delightful experience.') }}
                </p>
                <p class="text-lg text-stone-600 mb-8 leading-relaxed">
                    {{ __('From classic flavors to innovative designs, we create cakes that make your celebrations memorable. Every cake is crafted with attention to detail and the finest ingredients.') }}
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('about') }}" class="btn-primary-modern">
                        {{ __('Our Story') }}
                        <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                    <a href="{{ route('about') }}#team" class="btn-hero-outline inline-flex items-center justify-center px-6 py-3 font-semibold rounded-xl">{{ __('Meet the Team') }}</a>
                </div>
            </div>
            <div class="relative">
                <div class="aspect-square rounded-3xl overflow-hidden bg-gradient-to-br from-amber-100/80 to-stone-200/60 flex items-center justify-center p-16">
                    <svg class="w-full max-w-xs text-stone-400/40" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="100" cy="155" rx="70" ry="12" fill="currentColor" opacity="0.2"/>
                        <rect x="55" y="95" width="90" height="55" rx="8" fill="currentColor" opacity="0.25"/>
                        <rect x="60" y="65" width="80" height="35" rx="6" fill="currentColor" opacity="0.35"/>
                        <rect x="70" y="40" width="60" height="30" rx="4" fill="currentColor" opacity="0.4"/>
                        <circle cx="100" cy="55" r="8" fill="currentColor" opacity="0.5"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CTA strip --}}
<section class="py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="theme-cta-strip text-center">
            <h2 class="theme-cta-title text-3xl sm:text-4xl lg:text-5xl mb-4">{{ __('Ready for something extraordinary?') }}</h2>
            <p class="text-white/90 text-lg mb-8 max-w-2xl mx-auto">{{ __('We invite you to order your favorite cake or get in touch for your special event.') }}</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('products.index') }}" class="btn-cta-gold inline-flex items-center justify-center px-8 py-4 font-semibold text-lg rounded-xl">
                    {{ __('Explore Menu') }}
                </a>
                <a href="{{ route('contact.index') }}" class="btn-cta-outline inline-flex items-center justify-center px-8 py-4 font-semibold text-lg rounded-xl">
                    {{ __('Contact Us') }}
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Testimonials --}}
@if($testimonials->isNotEmpty())
<section class="py-20 lg:py-28 bg-[#f5f5f0]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <p class="text-sm font-medium text-stone-500 uppercase tracking-wider mb-2">{{ __('Testimonials') }}</p>
            <h2 class="heading-display text-4xl sm:text-5xl mb-4">{{ __('What Our Customers Say') }}</h2>
            <p class="text-xl text-stone-600 max-w-2xl mx-auto">{{ __('Real feedback from our happy customers') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($testimonials as $t)
                <div class="card-modern p-8">
                    <div class="flex items-center space-x-1 text-[#C5A059] mb-4">
                        @for($i = 0; $i < (int) $t->rating; $i++)
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        @endfor
                    </div>
                    <p class="text-stone-700 mb-6 leading-relaxed">"{{ $t->review }}"</p>
                    <div class="flex items-center pt-6 border-t border-stone-200">
                        <div class="lumiere-avatar w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            {{ $t->initials }}
                        </div>
                        <div class="ml-4">
                            <p class="font-semibold text-stone-900">{{ $t->customer_name }}</p>
                            @if($t->is_verified)
                                <p class="text-sm text-stone-500">{{ __('Verified Customer') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Explore Our Collection --}}
<section class="py-20 lg:py-28 bg-[#ebebe6]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 mb-10">
            <div>
                <h2 class="heading-display text-4xl sm:text-5xl mb-2">{{ __('Explore Our Collection') }}</h2>
                <p class="text-xl text-stone-600">{{ __('Discover our newest cake creations') }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="lumiere-btn-viewall inline-flex items-center justify-center px-6 py-3 font-semibold rounded-xl shrink-0">
                {{ __('View All') }}
                <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @forelse($products->take(6) as $product)
                <div>@include('products._card', ['product' => $product])</div>
            @empty
                <p class="lg:col-span-3 text-center text-stone-500 py-12">{{ __('No products available yet.') }}</p>
            @endforelse
        </div>
    </div>
</section>
@endsection

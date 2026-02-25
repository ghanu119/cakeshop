@extends('layouts.app')

@section('content')
@themeIs('lumiere')
{{-- Lumiere hero: beige, left-aligned, elegant serif --}}
<section class="hero-lumiere relative flex items-center overflow-hidden">
    <div class="hero-overlay absolute inset-0 bg-black/20"></div>
    <div class="relative z-10 w-full mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div class="hero-content" data-aos="fade-up">
                <p class="hero-label">{{ __('Artisan Bakery') }}</p>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl mb-4">
                    <span class="hero-title-part1">{{ __('Sweetness') }}</span>
                    <span class="hero-title-part2"> {{ __('Redefined') }}</span>
                </h1>
                <p class="hero-desc text-lg sm:text-xl mb-8 max-w-lg leading-relaxed">
                    {{ __('Experience the art of French patisserie and handcrafted cakes made with intention and the finest ingredients.') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('products.index') }}" class="btn-hero-primary inline-flex items-center justify-center px-8 py-4 font-semibold text-lg rounded-xl shadow-lg transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--theme-hero-bg)]">
                        {{ __('Explore Collection') }}
                        <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                    <a href="{{ route('about') }}" class="btn-hero-outline inline-flex items-center justify-center px-8 py-4 font-semibold text-lg rounded-xl transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2">
                        {{ __('Our Story') }}
                    </a>
                </div>
            </div>
            <div class="hidden lg:block relative" data-aos="fade-left">
                <div class="aspect-[4/3] rounded-2xl overflow-hidden bg-gradient-to-br from-stone-200 to-stone-300 flex items-center justify-center">
                    <svg class="w-3/4 h-3/4 text-stone-400/60" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
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
    <a href="#why-choose-us" class="absolute bottom-8 left-1/2 -translate-x-1/2 text-stone-500 hover:text-stone-700 transition-colors rounded-full" aria-label="{{ __('Scroll down') }}">
        <svg class="w-8 h-8 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
    </a>
</section>
@endthemeIs

@unless(theme() === 'lumiere')
{{-- Default hero --}}
<section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden bg-gradient-hero">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="absolute inset-0 opacity-30" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.15\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/svg%3E');"></div>
    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
        <div class="mx-auto max-w-4xl text-center text-white" data-aos="fade-up">
            <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight mb-6 drop-shadow-lg">
                {{ settings('site_name') ?: config('app.name') }}
            </h1>
            <p class="text-xl sm:text-2xl lg:text-3xl font-light mb-4 text-white/95">
                {{ __('Fresh cakes for every occasion') }}
            </p>
            <p class="text-lg sm:text-xl mb-10 text-white/85 max-w-2xl mx-auto leading-relaxed">
                {{ __('Indulge in our handcrafted cakes made with the finest ingredients. Perfect for birthdays, weddings, anniversaries, and every special moment.') }}
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('products.index') }}" class="group inline-flex items-center px-8 py-4 bg-white text-amber-600 font-bold text-lg rounded-xl shadow-xl hover:bg-gray-50 transition-all duration-200 hover:scale-105 hover:shadow-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-amber-500">
                    {{ __('Browse Cakes') }}
                    <svg class="ml-2 h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
                <a href="{{ route('contact.index') }}" class="inline-flex items-center px-8 py-4 bg-transparent border-2 border-white text-white font-bold text-lg rounded-xl hover:bg-white hover:text-amber-600 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-amber-500">
                    {{ __('Contact Us') }}
                </a>
            </div>
        </div>
    </div>
    <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white to-transparent"></div>
    <a href="#why-choose-us" class="absolute bottom-8 left-1/2 -translate-x-1/2 text-white/90 hover:text-white transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-amber-500 rounded-full" aria-label="{{ __('Scroll down') }}">
        <svg class="w-8 h-8 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
        </svg>
    </a>
</section>
@endunless

{{-- Why Choose Us / Features Section --}}
<section id="why-choose-us" class="py-20 lg:py-28 section-light scroll-mt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            @themeIs('lumiere')
            <h2 class="heading-display text-4xl sm:text-5xl mb-4">{{ __('Crafted with Intention') }}</h2>
            <p class="text-xl text-stone-600 max-w-2xl mx-auto">{{ __('Organic ingredients, artisanal process, premium recipes—every detail matters.') }}</p>
            @endthemeIs
            @unless(theme() === 'lumiere')
            <span class="badge-warm mb-4">{{ __('Why Choose Us') }}</span>
            <h2 class="heading-display text-4xl sm:text-5xl mb-4">{{ __('We are committed to delivering excellence') }}</h2>
            <p class="text-xl text-stone-600 max-w-2xl mx-auto">{{ __('Every bite is crafted with passion and the finest ingredients') }}</p>
            @endunless
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @forelse($features as $feature)
                <div class="group card-modern p-8 relative overflow-hidden" data-aos="fade-up" data-aos-delay="{{ $loop->index * 80 }}">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-500 to-orange-500 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                    <div class="w-14 h-14 mx-auto mb-6 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform duration-200">
                        @if($feature->icon === 'shield-check')
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        @elseif($feature->icon === 'clock')
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @elseif($feature->icon === 'check-circle')
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @elseif($feature->icon === 'shopping-cart')
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        @else
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </div>
                    <h3 class="text-xl font-display font-bold text-stone-900 text-center mb-3">{{ $feature->title }}</h3>
                    <p class="text-stone-600 text-center leading-relaxed text-sm">{{ $feature->description }}</p>
                </div>
            @empty
                <div class="group card-modern p-8" data-aos="fade-up"><div class="w-14 h-14 mx-auto mb-6 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-center text-white shadow-lg"><svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg></div><h3 class="text-xl font-display font-bold text-stone-900 text-center mb-3">{{ __('Premium Quality') }}</h3><p class="text-stone-600 text-center leading-relaxed text-sm">{{ __('Made with finest ingredients and traditional recipes') }}</p></div>
                <div class="group card-modern p-8" data-aos="fade-up" data-aos-delay="80"><div class="w-14 h-14 mx-auto mb-6 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-center text-white shadow-lg"><svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div><h3 class="text-xl font-display font-bold text-stone-900 text-center mb-3">{{ __('Fresh Daily') }}</h3><p class="text-stone-600 text-center leading-relaxed text-sm">{{ __('Baked fresh every morning for maximum flavor') }}</p></div>
                <div class="group card-modern p-8" data-aos="fade-up" data-aos-delay="160"><div class="w-14 h-14 mx-auto mb-6 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-center text-white shadow-lg"><svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div><h3 class="text-xl font-display font-bold text-stone-900 text-center mb-3">{{ __('Custom Orders') }}</h3><p class="text-stone-600 text-center leading-relaxed text-sm">{{ __('Personalized cakes for your special occasions') }}</p></div>
                <div class="group card-modern p-8" data-aos="fade-up" data-aos-delay="240"><div class="w-14 h-14 mx-auto mb-6 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-center text-white shadow-lg"><svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg></div><h3 class="text-xl font-display font-bold text-stone-900 text-center mb-3">{{ __('Fast Delivery') }}</h3><p class="text-stone-600 text-center leading-relaxed text-sm">{{ __('Quick and reliable delivery to your doorstep') }}</p></div>
            @endforelse
        </div>
    </div>
</section>

{{-- Top Feature Products Section --}}
<section class="py-20 lg:py-28 section-warm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 mb-12" data-aos="fade-up">
            <div class="text-center sm:text-left">
                @themeIs('lumiere')
                <h2 class="heading-display text-4xl sm:text-5xl mb-2">{{ __('Seasonal Favorites') }}</h2>
                <p class="text-xl text-stone-600">{{ __('Handpicked cakes for the season') }}</p>
                @endthemeIs
                @unless(theme() === 'lumiere')
                <span class="badge-warm mb-4">{{ __('Top Feature Products') }}</span>
                <h2 class="heading-display text-4xl sm:text-5xl mb-2">{{ __('Most Popular Cakes') }}</h2>
                <p class="text-xl text-stone-600">{{ __('Discover our most beloved cake creations') }}</p>
                @endunless
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-white/90 border-2 border-amber-500 text-amber-700 font-semibold rounded-xl hover:bg-amber-50 hover:shadow-md transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 shrink-0">
                {{ __('All Products') }}
                <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>
        <div class="js-highlights-slider grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
            @forelse($highlights->take(4) as $product)
                <div class="px-2" data-aos="fade-up" data-aos-delay="{{ $loop->index * 60 }}">
                    @include('products._card', ['product' => $product])
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-4 flex flex-col items-center justify-center py-16 px-6 rounded-2xl card-modern" data-aos="fade-up">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center text-amber-600 mb-6">
                        <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    <p class="text-lg font-semibold text-stone-900 mb-2">{{ __('No featured products yet') }}</p>
                    <p class="text-stone-600 text-center mb-6 max-w-md">{{ __('Browse our full collection of cakes and pastries') }}</p>
                    <a href="{{ route('products.index') }}" class="btn-primary-modern">
                        {{ __('All Products') }}
                        <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- About Company Section --}}
<section class="py-20 lg:py-28 section-mesh">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div data-aos="fade-right">
                <span class="badge-warm mb-4">{{ __('About Us') }}</span>
                <h2 class="heading-display text-4xl sm:text-5xl mb-6">{{ __('About Our Bakery') }}</h2>
                <p class="text-lg text-stone-600 mb-6 leading-relaxed">
                    {{ __('With years of experience in the art of baking, we bring you the finest cakes made with love and passion. Our commitment to quality and freshness ensures every bite is a delightful experience.') }}
                </p>
                <p class="text-lg text-stone-600 mb-8 leading-relaxed">
                    {{ __('From classic flavors to innovative designs, we create cakes that make your celebrations memorable. Every cake is crafted with attention to detail and the finest ingredients.') }}
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('about') }}" class="btn-primary-modern">
                        {{ __('Learn More') }}
                        <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                    <a href="{{ route('contact.index') }}" class="inline-flex items-center px-6 py-3 bg-white border-2 border-amber-200 text-stone-700 font-semibold rounded-xl hover:border-amber-500 hover:text-amber-600 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 shadow-sm">
                        {{ __('Contact Us') }}
                    </a>
                </div>
            </div>
            <div class="relative" data-aos="fade-left">
                <div class="aspect-square rounded-3xl overflow-hidden shadow-xl bg-gradient-to-br from-amber-100 via-orange-100 to-amber-200 border border-amber-200/50">
                    <div class="h-full w-full flex items-center justify-center p-16">
                        {{-- Layered cake / bakery illustration --}}
                        <svg class="w-full h-full max-w-xs text-amber-600/40" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <ellipse cx="100" cy="155" rx="70" ry="12" fill="currentColor" opacity="0.2"/>
                            <rect x="55" y="95" width="90" height="55" rx="8" fill="currentColor" opacity="0.25"/>
                            <rect x="60" y="65" width="80" height="35" rx="6" fill="currentColor" opacity="0.35"/>
                            <rect x="70" y="40" width="60" height="30" rx="4" fill="currentColor" opacity="0.4"/>
                            <circle cx="100" cy="55" r="8" fill="currentColor" opacity="0.5"/>
                            <circle cx="75" cy="50" r="5" fill="currentColor" opacity="0.35"/>
                            <circle cx="125" cy="52" r="5" fill="currentColor" opacity="0.35"/>
                        </svg>
                    </div>
                </div>
                <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-amber-400/20 rounded-full blur-2xl"></div>
            </div>
        </div>
    </div>
</section>

@themeIs('lumiere')
{{-- Lumiere: mid-page CTA strip --}}
<section class="py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="theme-cta-strip text-center" data-aos="fade-up">
            <h2 class="theme-cta-title text-3xl sm:text-4xl lg:text-5xl mb-4">{{ __('Ready for something extraordinary?') }}</h2>
            <p class="text-white/90 text-lg mb-8 max-w-2xl mx-auto">{{ __('We invite you to order your favorite cake or get in touch for your special event.') }}</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('contact.index') }}" class="btn-cta-gold inline-flex items-center justify-center px-8 py-4 font-semibold text-lg rounded-xl transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--theme-cta-bg)]">
                    {{ __('Book a Consultation') }}
                </a>
                <a href="{{ route('products.index') }}" class="btn-cta-outline inline-flex items-center justify-center px-8 py-4 font-semibold text-lg rounded-xl transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--theme-cta-bg)]">
                    {{ __('Order Online') }}
                </a>
            </div>
        </div>
    </div>
</section>
@endthemeIs

{{-- Customer Reviews Section --}}
@if($testimonials->isNotEmpty())
<section class="py-20 lg:py-28 section-warm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <span class="badge-warm mb-4">{{ __('Testimonials') }}</span>
            <h2 class="heading-display text-4xl sm:text-5xl mb-4">{{ __('What Our Customers Say') }}</h2>
            <p class="text-xl text-stone-600 max-w-2xl mx-auto">{{ __('Real feedback from our happy customers') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($testimonials as $testimonial)
                <div class="card-modern p-8" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    <div class="flex items-center space-x-1 text-amber-500 mb-4">
                        @for($i = 0; $i < $testimonial->rating; $i++)
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20">
                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                            </svg>
                        @endfor
                    </div>
                    <p class="text-stone-700 mb-6 leading-relaxed italic">"{{ $testimonial->review }}"</p>
                    <div class="flex items-center pt-6 border-t border-amber-100/80">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                            {{ $testimonial->initials }}
                        </div>
                        <div class="ml-4">
                            <p class="font-display font-bold text-stone-900">{{ $testimonial->customer_name }}</p>
                            @if($testimonial->is_verified)
                                <p class="text-sm text-gray-500 flex items-center">
                                    <svg class="h-4 w-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Verified Customer') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Latest Products Section --}}
<section class="py-20 lg:py-28 bg-gray-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 mb-12">
            <div class="text-center sm:text-left">
                <span class="inline-block px-4 py-2 bg-amber-100 text-amber-700 rounded-full text-sm font-semibold mb-4 tracking-wide">{{ __('Latest Products') }}</span>
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-2">{{ __('Explore Our Collection') }}</h2>
                <p class="text-xl text-gray-600">{{ __('Discover our newest cake creations') }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 shrink-0">
                {{ __('All Products') }}
                <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @forelse($products->take(6) as $product)
                <div data-aos="fade-up" data-aos-delay="{{ $loop->index * 80 }}">
                    @include('products._card', ['product' => $product])
                </div>
            @empty
                <div class="lg:col-span-3 flex flex-col items-center justify-center py-20 px-6 rounded-2xl card-modern" data-aos="fade-up">
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center text-amber-600 mb-6">
                        <svg class="h-14 w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    <p class="text-xl font-semibold text-stone-900 mb-2">{{ __('No products available') }}</p>
                    <p class="text-stone-600 text-center mb-8 max-w-md">{{ __('Check back soon for new cake creations—or browse our full collection.') }}</p>
                    <a href="{{ route('products.index') }}" class="btn-primary-modern px-8 py-4 border-0">
                        {{ __('All Products') }}
                        <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection

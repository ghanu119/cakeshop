@extends('layouts.app')

@section('title', __('About us') . ' - ' . (settings('site_name') ?: config('app.name')))

@section('content')
@php
    $productItems = [
        __('Sandwich bread'),
        __('Pav bhaji buns'),
        __('Pizza bases'),
        __('Toast'),
        __('Cake rusk'),
        __('Cakes and customized cakes'),
        __('Pastries'),
        __('Puffs'),
        __('Different varieties of khari'),
        __('Other freshly prepared bakery products'),
    ];

    $promises = [
        __('Freshly prepared bakery products'),
        __('Consistent taste and quality'),
        __('Carefully selected ingredients'),
        __('Hygienic preparation'),
        __('A wide variety for every age and occasion'),
        __('Friendly and dependable customer service'),
    ];
@endphp

{{-- Breadcrumb --}}
<section class="border-b border-stone-100 bg-white py-4">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center flex-wrap gap-x-2 gap-y-1 text-sm font-medium text-stone-500">
            <a href="{{ route('home') }}" class="hover:text-amber-600 transition-colors">{{ __('Home') }}</a>
            <svg class="h-4 w-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="text-stone-900">{{ __('About us') }}</span>
        </nav>
    </div>
</section>

{{-- Hero intro --}}
<section class="py-20 lg:py-28 section-mesh relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-amber-200/20 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
        <div class="absolute bottom-10 left-1/4 w-72 h-72 bg-orange-200/20 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl">
            <span class="inline-block py-1.5 px-4 rounded-full bg-amber-100 text-amber-800 text-sm font-bold tracking-wider mb-6 border border-amber-200 shadow-sm">{{ __('About Us') }}</span>
            <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-stone-900 mb-6 drop-shadow-sm leading-tight">
                {{ __('About Better Buns Live Bakery') }}
            </h1>
            <p class="text-xl sm:text-2xl text-stone-600 leading-relaxed font-light">
                {{ __('Baking freshness since August 2015 with fresh, delicious and dependable bakery products for every day and every celebration.') }}
            </p>
        </div>
    </div>
</section>

{{-- Our Story milestones --}}
<section class="section-warm py-20 lg:py-28 relative">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-stone-900 mb-4">{{ __('Baking Freshness Since August 2015') }}</h2>
            <p class="text-xl text-stone-500 max-w-3xl mx-auto font-light">{{ __('Better Buns Live Bakery began its journey in Rajkot in August 2015 with a simple vision: to provide fresh, delicious and dependable bakery products that families can enjoy every day.') }}</p>
        </div>
        @include('pages.partials._our-story-timeline')
    </div>
</section>

{{-- Product range --}}
<section class="py-20 lg:py-28 section-light">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-[0.95fr_1.05fr] gap-12 lg:gap-16 items-start">
            <div data-aos="fade-up">
                <span class="badge-warm mb-4">{{ __('Our Range') }}</span>
                <h2 class="heading-display text-4xl sm:text-5xl mb-5">{{ __('From Everyday Breads to Special Celebrations') }}</h2>
                <p class="text-xl text-stone-600 leading-relaxed">{{ __('What started as a passion for baking has grown into a trusted local bakery offering everyday bakery essentials, savoury snacks and celebration cakes.') }}</p>
                <p class="text-lg text-stone-500 leading-relaxed mt-6">{{ __('At Better Buns, we believe a bakery should be part of both your everyday meals and your most memorable celebrations.') }}</p>
            </div>

            <div class="card-modern p-8 sm:p-10 relative overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-500 to-orange-500 rounded-t-2xl"></div>
                <div class="relative">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach ($productItems as $item)
                            <div class="rounded-2xl bg-stone-50 px-4 py-4 text-stone-700 border border-stone-100">
                                <div class="flex items-start gap-3">
                                    <span class="mt-1.5 h-2.5 w-2.5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 flex-shrink-0"></span>
                                    <span class="text-base leading-relaxed">{{ $item }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 rounded-2xl bg-amber-50 px-6 py-6 border border-amber-100">
                        <h3 class="font-display text-2xl sm:text-3xl font-bold text-stone-900 mb-3">{{ __('Prepared for every moment') }}</h3>
                        <p class="text-stone-600 leading-relaxed">{{ __('From soft bread for your morning breakfast and fresh buns for family meals to customized cakes for birthdays, anniversaries and special occasions, we prepare products for every moment.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Freshness and care --}}
<section class="py-20 lg:py-28 bg-white relative">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mb-14" data-aos="fade-up">
            <span class="badge-warm mb-4">{{ __('Freshness First') }}</span>
            <h2 class="heading-display text-4xl sm:text-5xl mt-6 mb-5">{{ __('Freshly Made with Care') }}</h2>
            <p class="text-xl text-stone-600 leading-relaxed">{{ __('Freshness is at the heart of everything we do. Our products are prepared with carefully selected ingredients, attention to hygiene and consistent baking processes.') }}</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-[0.9fr_1.1fr] gap-10 lg:gap-12 items-start">
            <div class="card-modern p-8 sm:p-10" data-aos="fade-up">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-center text-white shadow-lg mb-6">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <h3 class="text-3xl sm:text-4xl font-display font-bold text-stone-900 mb-8">{{ __('We focus on delivering') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($promises as $promise)
                        <div class="rounded-2xl bg-stone-50 px-5 py-5 border border-stone-100">
                            <div class="flex items-start gap-3">
                                <span class="mt-1.5 h-2.5 w-2.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                                <span class="text-base leading-relaxed text-stone-700">{{ $promise }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6">
                <article class="card-modern p-8 sm:p-10" data-aos="fade-up" data-aos-delay="100">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700 mb-4">{{ __('Custom Cakes') }}</p>
                    <h3 class="text-3xl sm:text-4xl font-display font-bold text-stone-900 mb-4">{{ __('Customized cakes for your celebration') }}</h3>
                    <p class="text-lg text-stone-600 leading-relaxed">{{ __('For our cakes, we also offer customization according to your preferred theme, design, flavour and celebration.') }}</p>
                </article>

                <article class="card-modern p-8 sm:p-10" data-aos="fade-up" data-aos-delay="200">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500 mb-4">{{ __('Our Philosophy') }}</p>
                    <h3 class="text-3xl sm:text-4xl font-display font-bold text-stone-900 mb-4">{{ __('A bakery for daily life and big moments') }}</h3>
                    <p class="text-lg text-stone-600 leading-relaxed">{{ __('We do not want to be known only as a cake shop. We want Better Buns to be the bakery our customers can trust for their daily bread, evening snacks, family gatherings and special celebrations.') }}</p>
                    <p class="text-lg text-stone-600 leading-relaxed mt-5">{{ __('Every loaf, bun, toast, pastry, puff and cake is prepared with the same commitment to make every bite fresh, tasty and enjoyable.') }}</p>
                </article>
            </div>
        </div>
    </div>
</section>

{{-- Mission and vision --}}
<section class="py-20 lg:py-28 section-warm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <span class="badge-warm mb-4">{{ __('What Drives Us') }}</span>
            <h2 class="heading-display text-4xl sm:text-5xl mb-4">{{ __('Our Mission and Vision') }}</h2>
            <p class="text-xl text-stone-500 max-w-3xl mx-auto font-light">{{ __('What drives Better Buns every day and where we want to go next.') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-10">
            <div class="card-modern p-8 sm:p-10" data-aos="fade-up">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700 mb-4">{{ __('Mission') }}</p>
                <h3 class="text-3xl sm:text-4xl font-display font-bold text-stone-900 mb-4">{{ __('Our Mission') }}</h3>
                <p class="text-lg text-stone-600 leading-relaxed">{{ __('Our mission is to make fresh, high-quality bakery products easily available to every family while maintaining excellent taste, hygiene and customer satisfaction.') }}</p>
            </div>
            <div class="card-modern p-8 sm:p-10" data-aos="fade-up" data-aos-delay="100">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700 mb-4">{{ __('Vision') }}</p>
                <h3 class="text-3xl sm:text-4xl font-display font-bold text-stone-900 mb-4">{{ __('Our Vision') }}</h3>
                <p class="text-lg text-stone-600 leading-relaxed">{{ __('Our vision is to become one of Rajkot’s most trusted bakery brands, known for fresh everyday bakery products, innovative cakes and dependable service.') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Visual + CTA block --}}
<section class="py-20 lg:py-28 section-mesh relative overflow-hidden">
    <!-- Soft background glow effect -->
    <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-gradient-to-tl from-amber-100/40 to-transparent rounded-full blur-3xl opacity-60 pointer-events-none"></div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="order-2 lg:order-1 relative" data-aos="fade-right">
                <div class="aspect-square max-w-md mx-auto lg:mx-0 rounded-3xl overflow-hidden shadow-xl bg-gradient-to-br from-amber-100 via-orange-100 to-amber-200 relative border-8 border-white">
                    <div class="absolute inset-0 bg-white/20 backdrop-blur-sm mix-blend-overlay"></div>
                    <div class="h-full w-full flex items-center justify-center p-12 relative z-10">
                        <svg class="w-full h-full max-w-[200px] text-amber-600/60 drop-shadow-sm" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <ellipse cx="100" cy="165" rx="80" ry="12" fill="currentColor" opacity="0.1"/>
                            <rect x="55" y="95" width="90" height="65" rx="8" fill="currentColor" opacity="0.3"/>
                            <rect x="60" y="65" width="80" height="35" rx="6" fill="currentColor" opacity="0.4"/>
                            <rect x="70" y="40" width="60" height="30" rx="4" fill="currentColor" opacity="0.5"/>
                            <path d="M100 20C100 20 90 35 100 40C110 35 100 20 100 20Z" fill="currentColor" opacity="0.6"/>
                            <circle cx="100" cy="55" r="8" fill="currentColor" opacity="0.6"/>
                            <circle cx="75" cy="50" r="5" fill="currentColor" opacity="0.4"/>
                            <circle cx="125" cy="52" r="5" fill="currentColor" opacity="0.4"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="order-1 lg:order-2 text-center lg:text-left" data-aos="fade-left">
                <span class="badge-warm mb-4">{{ __('Thank You') }}</span>
                <h2 class="heading-display text-4xl sm:text-5xl mb-6 leading-tight">{{ __('Thank You for Being Part of Our Journey') }}</h2>
                <p class="text-xl text-stone-500 mb-6 leading-relaxed font-light">{{ __('Since August 2015, the love and support of our customers have helped Better Buns grow and improve. We are grateful to every customer who chooses us for their everyday bakery needs and special celebrations.') }}</p>
                <p class="text-lg text-stone-700 mb-10 font-medium">{{ __('Better Buns Live Bakery — Fresh Every Day, Special for Every Occasion.') }}</p>
                <a href="{{ route('contact.index') }}" class="btn-primary-modern w-full sm:w-auto">
                    {{ __('Contact us') }}
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    // Initialize AOS if it exists (assuming it's loaded in app.blade.php as used in home.blade.php)
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof AOS !== 'undefined') {
            AOS.init({
                once: true,
                offset: 50,
                duration: 800,
                easing: 'ease-out-cubic',
            });
        }
    });
</script>
@endpush
@endsection

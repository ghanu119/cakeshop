@extends('layouts.app')

@section('title', __('About us') . ' - ' . (settings('site_name') ?: config('app.name')))

@section('content')
@php
    $siteName = settings('site_name') ?: config('app.name');
@endphp

{{-- Breadcrumb --}}
<nav class="border-b border-stone-200/60 bg-[#f5f5f0] py-3">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2 text-sm text-stone-500">
            <a href="{{ route('home') }}" class="hover:text-stone-800 transition-colors">{{ __('Home') }}</a>
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="text-stone-800 font-medium">{{ __('About us') }}</span>
        </div>
    </div>
</nav>

{{-- Hero: About us pill + heading + intro --}}
<section class="bg-[#f5f5f0] py-12 lg:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <span class="inline-block rounded-full bg-[#5A5A40] px-4 py-2 text-sm font-semibold text-white tracking-wide mb-4">{{ __('About us') }}</span>
            <h1 class="heading-display text-4xl sm:text-5xl lg:text-6xl text-stone-900 mb-6">{{ __('About') }} {{ $siteName }}</h1>
            <p class="text-xl text-stone-600 leading-relaxed">{{ __('We aim to bring fresh, delicious cakes to every celebration and to be the trusted choice for quality cakes.') }}</p>
        </div>
    </div>
</section>

{{-- Our Story: timeline with gold circular icons --}}
<section class="bg-[#f5f5f0] py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="heading-display text-3xl sm:text-4xl text-stone-900 mb-3">{{ __('Our Story') }}</h2>
            <p class="text-lg text-stone-600 max-w-2xl mx-auto">{{ __('Every milestone on our journey.') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-6">
            @foreach([
                ['title' => __('The beginning'), 'text' => __('We opened our first kitchen with a dream to bring artisan cakes to every celebration.'), 'icon' => 'star'],
                ['title' => __('Growing roots'), 'text' => __('Expanded our range with seasonal favorites and custom wedding cakes.'), 'icon' => 'plant'],
                ['title' => __('Area of recognition'), 'text' => __('Our recipes and quality were recognized by local food critics and community.'), 'icon' => 'award'],
                ['title' => __('Innovative & exceptional'), 'text' => __('We continue to craft with intention—pure ingredients, patient process, and a touch of modern magic.'), 'icon' => 'bulb'],
            ] as $item)
            <div class="flex flex-col items-center text-center">
                <div class="lumiere-icon-gold w-14 h-14 rounded-full flex items-center justify-center text-white mb-4 flex-shrink-0">
                    @if($item['icon'] === 'star')
                        <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    @elseif($item['icon'] === 'plant')
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    @elseif($item['icon'] === 'award')
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    @else
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    @endif
                </div>
                <h3 class="font-display text-lg font-bold text-stone-900 mb-2">{{ $item['title'] }}</h3>
                <p class="text-stone-600 text-sm leading-relaxed">{{ $item['text'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Core values: 3 white cards, gold icons --}}
<section class="bg-[#ebebe6] py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="heading-display text-3xl sm:text-4xl text-stone-900 mb-3">{{ __('Core values') }}</h2>
            <p class="text-lg text-stone-600 max-w-2xl mx-auto">{{ __('What we stand for.') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="card-modern p-8 text-center">
                <div class="lumiere-icon-gold w-14 h-14 mx-auto mb-4 rounded-full flex items-center justify-center text-white">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <h3 class="font-display text-xl font-bold text-stone-900 mb-2">{{ __('Pure ingredients') }}</h3>
                <p class="text-stone-600 text-sm leading-relaxed">{{ __('We source the finest, natural ingredients. No shortcuts—just quality that you can taste.') }}</p>
            </div>
            <div class="card-modern p-8 text-center">
                <div class="lumiere-icon-gold w-14 h-14 mx-auto mb-4 rounded-full flex items-center justify-center text-white">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-display text-xl font-bold text-stone-900 mb-2">{{ __('Patient craft') }}</h3>
                <p class="text-stone-600 text-sm leading-relaxed">{{ __('Every cake is made with time and care. Traditional methods meet consistent excellence.') }}</p>
            </div>
            <div class="card-modern p-8 text-center">
                <div class="lumiere-icon-gold w-14 h-14 mx-auto mb-4 rounded-full flex items-center justify-center text-white">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                </div>
                <h3 class="font-display text-xl font-bold text-stone-900 mb-2">{{ __('Modern magic') }}</h3>
                <p class="text-stone-600 text-sm leading-relaxed">{{ __('Innovative designs and flavors that surprise and delight, while staying true to our roots.') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Our mission, vision & values: 3 cards with bullet list for values --}}
<section class="bg-[#f5f5f0] py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="heading-display text-3xl sm:text-4xl text-stone-900 mb-3">{{ __('Our mission, vision & values') }}</h2>
            <p class="text-lg text-stone-600 max-w-2xl mx-auto">{{ __('What drives us every day.') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="card-modern p-8 text-center">
                <div class="lumiere-icon-gold w-14 h-14 mx-auto mb-4 rounded-full flex items-center justify-center text-white">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-display text-xl font-bold text-stone-900 mb-3">{{ __('Our mission') }}</h3>
                <p class="text-stone-600 text-sm leading-relaxed">{{ __('We aim to bring fresh, delicious cakes to every celebration.') }}</p>
            </div>
            <div class="card-modern p-8 text-center">
                <div class="lumiere-icon-gold w-14 h-14 mx-auto mb-4 rounded-full flex items-center justify-center text-white">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <h3 class="font-display text-xl font-bold text-stone-900 mb-3">{{ __('Our vision') }}</h3>
                <p class="text-stone-600 text-sm leading-relaxed">{{ __('To be the trusted choice for quality cakes.') }}</p>
            </div>
            <div class="card-modern p-8 text-center">
                <div class="lumiere-icon-gold w-14 h-14 mx-auto mb-4 rounded-full flex items-center justify-center text-white">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <h3 class="font-display text-xl font-bold text-stone-900 mb-3">{{ __('Our values') }}</h3>
                <ul class="space-y-2 text-stone-600 text-sm text-left max-w-[200px] mx-auto">
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-[#C5A059] flex-shrink-0"></span> {{ __('Quality ingredients') }}</li>
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-[#C5A059] flex-shrink-0"></span> {{ __('Friendly service') }}</li>
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-[#C5A059] flex-shrink-0"></span> {{ __('Transparency') }}</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- The team: gold circles with initials --}}
<section class="bg-[#ebebe6] py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="heading-display text-3xl sm:text-4xl text-stone-900 mb-3">{{ __('The team') }}</h2>
            <p class="text-lg text-stone-600 max-w-2xl mx-auto">{{ __('The people behind the magic.') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['initial' => 'H', 'role' => __('Head Pastry Chef'), 'bio' => __('Passionate about French patisserie and bringing artisan quality to every creation.')],
                ['initial' => 'L', 'role' => __('Lead Baker'), 'bio' => __('Decades of experience in traditional recipes and modern techniques.')],
                ['initial' => 'C', 'role' => __('Creative Director'), 'bio' => __('Designs our seasonal collections and custom cake experiences.')],
            ] as $member)
            <div class="card-modern p-8 text-center">
                <div class="lumiere-icon-gold w-20 h-20 mx-auto mb-4 rounded-full flex items-center justify-center text-white text-2xl font-display font-bold">{{ $member['initial'] }}.</div>
                <h3 class="font-display text-lg font-bold text-stone-900">{{ $member['role'] }}</h3>
                <p class="mt-2 text-stone-600 text-sm leading-relaxed">{{ $member['bio'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Get in touch: gradient card + CTA --}}
<section class="bg-[#f5f5f0] py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div class="lumiere-about-cta-card aspect-square max-w-md rounded-3xl overflow-hidden shadow-xl flex items-center justify-center p-12">
                <svg class="w-full h-full max-w-[200px] text-stone-400/50" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="100" cy="155" rx="70" ry="12" fill="currentColor" opacity="0.25"/>
                    <rect x="55" y="95" width="90" height="55" rx="8" fill="currentColor" opacity="0.3"/>
                    <rect x="60" y="65" width="80" height="35" rx="6" fill="currentColor" opacity="0.4"/>
                    <rect x="70" y="40" width="60" height="30" rx="4" fill="currentColor" opacity="0.45"/>
                    <circle cx="100" cy="55" r="8" fill="currentColor" opacity="0.5"/>
                    <circle cx="75" cy="50" r="5" fill="currentColor" opacity="0.4"/>
                    <circle cx="125" cy="52" r="5" fill="currentColor" opacity="0.4"/>
                </svg>
            </div>
            <div>
                <h2 class="heading-display text-3xl sm:text-4xl text-stone-900 mb-4">{{ __('Get in touch') }}</h2>
                <p class="text-lg text-stone-600 mb-8 leading-relaxed">{{ __("Have a question? We'd love to hear from you.") }}</p>
                <a href="{{ route('contact.index') }}" class="btn-primary-modern inline-flex items-center gap-2">
                    {{ __('Contact us') }}
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

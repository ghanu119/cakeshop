@extends('layouts.app')

@section('title', __('About us') . ' - ' . (settings('site_name') ?: config('app.name')))

@section('content')
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
<section class="py-20 lg:py-32 bg-gradient-to-b from-amber-50 to-white relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-amber-200/20 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
        <div class="absolute bottom-10 left-1/4 w-72 h-72 bg-orange-200/20 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl">
            <span class="inline-block py-1.5 px-4 rounded-full bg-amber-100 text-amber-800 text-sm font-bold tracking-wider mb-6 border border-amber-200 shadow-sm">{{ __('About Us') }}</span>
            <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-stone-900 mb-6 drop-shadow-sm leading-tight">
                {{ __('About') }} <span class="text-amber-600">{{ settings('site_name') ?: config('app.name') }}</span>
            </h1>
            <p class="text-xl sm:text-2xl text-stone-600 leading-relaxed font-light">
                {{ __('We aim to bring fresh, delicious cakes to every celebration and to be the trusted choice for quality cakes.') }}
            </p>
        </div>
    </div>
</section>

{{-- Heritage timeline (Our Story) --}}
<section class="py-20 lg:py-28 bg-white relative">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-stone-900 mb-4">{{ __('Our Story') }}</h2>
            <p class="text-xl text-stone-500 max-w-2xl mx-auto font-light">{{ __('Key milestones on our journey') }}</p>
        </div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-0">
            <!-- Center Line -->
            <div class="absolute left-8 sm:left-1/2 top-0 bottom-0 w-0.5 -translate-x-px bg-amber-200"></div>
            
            <!-- Increased space-y to push items further apart -->
            <div class="space-y-24 sm:space-y-32">
                @foreach([
                    ['title' => __('The beginning'), 'text' => __('We opened our first kitchen with a dream to bring artisan cakes to every celebration.')],
                    ['title' => __('Growing roots'), 'text' => __('Expanded our range with seasonal favorites and custom wedding cakes.')],
                    ['title' => __('Award recognition'), 'text' => __('Our recipes and quality were recognized by local food critics and community.')],
                    ['title' => __('Sweetness redefined'), 'text' => __('We continue to craft with intention—pure ingredients, patient process, and a touch of modern magic.')],
                ] as $milestone)
                <div class="relative w-full group" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    
                    <!-- Icon -->
                    <div class="absolute left-8 sm:left-1/2 top-0 sm:top-1/2 w-12 h-12 -translate-x-1/2 sm:-translate-y-1/2 rounded-full bg-white border-4 border-amber-100 shadow-sm flex items-center justify-center text-amber-500 group-hover:border-amber-400 group-hover:bg-amber-50 transition-all duration-300 z-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>
                    </div>

                    <!-- Content -->
                    <div class="w-full flex @if($loop->odd) sm:justify-start @else sm:justify-end @endif">
                        <!-- Added more padding to push text further away from the center line -->
                        <div class="w-full sm:w-1/2 pl-20 sm:pl-0 @if($loop->odd) sm:pr-20 sm:text-right @else sm:pl-20 sm:text-left @endif pt-2 sm:pt-0">
                            <h3 class="font-display text-xl font-bold text-stone-900 mb-2">{{ $milestone['title'] }}</h3>
                            <p class="text-stone-500 leading-relaxed">{{ $milestone['text'] }}</p>
                        </div>
                    </div>

                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Core values: Pure Ingredients, Patient Craft, Modern Magic --}}
<section class="py-20 lg:py-28 bg-stone-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-stone-900 mb-4">{{ __('Core values') }}</h2>
            <p class="text-xl text-stone-500 max-w-2xl mx-auto font-light">{{ __('What we stand for') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-3xl p-10 text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(217,119,6,0.1)] border border-stone-100 transition-all duration-300 hover:-translate-y-2" data-aos="fade-up">
                <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                </div>
                <h3 class="font-display text-2xl font-bold text-stone-900 mb-3">{{ __('Pure ingredients') }}</h3>
                <p class="text-stone-500 leading-relaxed">{{ __('We source the finest, natural ingredients. No shortcuts—just quality that you can taste.') }}</p>
            </div>
            <div class="bg-white rounded-3xl p-10 text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(217,119,6,0.1)] border border-stone-100 transition-all duration-300 hover:-translate-y-2" data-aos="fade-up" data-aos-delay="100">
                <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="font-display text-2xl font-bold text-stone-900 mb-3">{{ __('Patient craft') }}</h3>
                <p class="text-stone-500 leading-relaxed">{{ __('Every cake is made with time and care. Traditional methods meet consistent excellence.') }}</p>
            </div>
            <div class="bg-white rounded-3xl p-10 text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(217,119,6,0.1)] border border-stone-100 transition-all duration-300 hover:-translate-y-2" data-aos="fade-up" data-aos-delay="200">
                <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                </div>
                <h3 class="font-display text-2xl font-bold text-stone-900 mb-3">{{ __('Modern magic') }}</h3>
                <p class="text-stone-500 leading-relaxed">{{ __('Innovative designs and flavors that surprise and delight, while staying true to our roots.') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Mission, Vision, Values – clear editorial layout --}}
<section class="py-20 lg:py-28 bg-white relative">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-stone-900 mb-4">{{ __('Our mission, vision & values') }}</h2>
            <p class="text-xl text-stone-500 max-w-2xl mx-auto font-light">{{ __('What drives us every day') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Column 1: Mission --}}
            <div class="bg-white rounded-3xl p-10 text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(217,119,6,0.1)] border border-stone-100 transition-all duration-300 hover:-translate-y-2" data-aos="fade-up">
                <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                </div>
                <h3 class="font-display text-2xl font-bold text-stone-900 mb-3">{{ __('Our mission') }}</h3>
                <p class="text-stone-500 leading-relaxed">{{ __('We aim to bring fresh, delicious cakes to every celebration.') }}</p>
            </div>

            {{-- Column 2: Vision --}}
            <div class="bg-white rounded-3xl p-10 text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(217,119,6,0.1)] border border-stone-100 transition-all duration-300 hover:-translate-y-2" data-aos="fade-up" data-aos-delay="100">
                <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
                <h3 class="font-display text-2xl font-bold text-stone-900 mb-3">{{ __('Our vision') }}</h3>
                <p class="text-stone-500 leading-relaxed">{{ __('To be the trusted choice for quality cakes.') }}</p>
            </div>

            {{-- Column 3: Values --}}
            <div class="bg-white rounded-3xl p-10 text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(217,119,6,0.1)] border border-stone-100 transition-all duration-300 hover:-translate-y-2" data-aos="fade-up" data-aos-delay="200">
                <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                </div>
                <h3 class="font-display text-2xl font-bold text-stone-900 mb-4">{{ __('Our values') }}</h3>
                <ul class="space-y-3 text-stone-500 text-left w-full max-w-[200px] mx-auto font-medium">
                    <li class="flex items-center gap-3"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span> {{ __('Quality ingredients') }}</li>
                    <li class="flex items-center gap-3"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span> {{ __('Best for everyone') }}</li>
                    <li class="flex items-center gap-3"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span> {{ __('Sustainability') }}</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- The team --}}
<section class="py-20 lg:py-28 bg-stone-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-stone-900 mb-4">{{ __('The team') }}</h2>
            <p class="text-xl text-stone-500 max-w-2xl mx-auto font-light">{{ __('The people behind the cakes') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach([
                ['name' => __('Head Pastry Chef'), 'role' => __('Founder & Head Pastry Chef'), 'bio' => __('Passionate about French patisserie and bringing artisan quality to every creation.')],
                ['name' => __('Lead Baker'), 'role' => __('Lead Baker'), 'bio' => __('Decades of experience in traditional recipes and modern techniques.')],
                ['name' => __('Creative Director'), 'role' => __('Creative Director'), 'bio' => __('Designs our seasonal collections and custom cake experiences.')],
            ] as $member)
            <div class="bg-white rounded-3xl p-8 text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-stone-100 transition-all duration-300 hover:shadow-[0_20px_40px_rgb(217,119,6,0.1)] hover:-translate-y-2" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-amber-100 to-orange-100 border-4 border-white shadow-sm flex items-center justify-center text-amber-600 text-3xl font-display font-bold">{{ Str::limit($member['name'], 1) }}</div>
                <h3 class="font-display text-2xl font-bold text-stone-900">{{ $member['name'] }}</h3>
                <p class="text-amber-600 font-medium mt-1 mb-4">{{ $member['role'] }}</p>
                <p class="text-stone-500 leading-relaxed">{{ $member['bio'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Visual + CTA block --}}
<section class="py-20 lg:py-32 bg-white relative overflow-hidden">
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
                <h2 class="font-display text-4xl sm:text-5xl font-bold text-stone-900 mb-6 leading-tight">{{ __('Get in touch') }}</h2>
                <p class="text-xl text-stone-500 mb-10 leading-relaxed font-light">{{ __('Have a question or want to place a custom order? We’d love to hear from you.') }}</p>
                <a href="{{ route('contact.index') }}" class="group inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold text-lg rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 w-full sm:w-auto">
                    {{ __('Contact us') }}
                    <svg class="ml-2 h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
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

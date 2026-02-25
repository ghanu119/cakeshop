@extends('layouts.app')

@section('title', __('About us') . ' - ' . (settings('site_name') ?: config('app.name')))

@section('content')
{{-- Breadcrumb --}}
<section class="border-b border-amber-100/80 bg-white py-4">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center flex-wrap gap-x-2 gap-y-1 text-sm">
            <a href="{{ route('home') }}" class="text-stone-500 hover:text-amber-600 transition-colors">{{ __('Home') }}</a>
            <svg class="h-4 w-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="text-stone-900 font-medium">{{ __('About us') }}</span>
        </nav>
    </div>
</section>

{{-- Hero intro --}}
<section class="section-light py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <span class="badge-warm mb-4">{{ __('About us') }}</span>
            <h1 class="heading-display text-4xl sm:text-5xl lg:text-6xl mb-6">{{ __('About') }} {{ settings('site_name') ?: config('app.name') }}</h1>
            <p class="text-xl text-stone-600 leading-relaxed">{{ __('We aim to bring fresh, delicious cakes to every celebration and to be the trusted choice for quality cakes.') }}</p>
        </div>
    </div>
</section>

{{-- Heritage timeline (Our Story) --}}
<section class="py-16 lg:py-24 section-warm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="heading-display text-3xl sm:text-4xl mb-3">{{ __('Our Story') }}</h2>
            <p class="text-lg text-stone-600 max-w-2xl mx-auto">{{ __('Key milestones on our journey') }}</p>
        </div>
        <div class="relative max-w-3xl mx-auto">
            <div class="absolute left-4 sm:left-1/2 top-0 bottom-0 w-0.5 -translate-x-px bg-amber-200/60"></div>
            <ul class="space-y-10">
                @foreach([
                    ['year' => '2018', 'title' => __('The beginning'), 'text' => __('We opened our first kitchen with a dream to bring artisan cakes to every celebration.')],
                    ['year' => '2020', 'title' => __('Growing roots'), 'text' => __('Expanded our range with seasonal favorites and custom wedding cakes.')],
                    ['year' => '2022', 'title' => __('Award recognition'), 'text' => __('Our recipes and quality were recognized by local food critics and community.')],
                    ['year' => __('Today'), 'title' => __('Sweetness redefined'), 'text' => __('We continue to craft with intention—pure ingredients, patient process, and a touch of modern magic.')],
                ] as $milestone)
                <li class="relative flex gap-6 sm:gap-10 pl-12 sm:pl-0 sm:odd:flex-row sm:even:flex-row-reverse sm:even:text-right">
                    <div class="absolute left-0 sm:left-1/2 w-8 h-8 -translate-x-1/2 rounded-full bg-amber-500 border-4 border-white shadow-md flex items-center justify-center text-xs font-bold text-white">{{ $milestone['year'] }}</div>
                    <div class="flex-1 sm:odd:pl-16 sm:even:pr-16 sm:even:pl-0">
                        <h3 class="font-display text-lg font-bold text-stone-900">{{ $milestone['title'] }}</h3>
                        <p class="mt-1 text-stone-600 text-sm leading-relaxed">{{ $milestone['text'] }}</p>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- Core values: Pure Ingredients, Patient Craft, Modern Magic --}}
<section class="py-16 lg:py-24 section-light">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="heading-display text-3xl sm:text-4xl mb-3">{{ __('Core values') }}</h2>
            <p class="text-lg text-stone-600 max-w-2xl mx-auto">{{ __('What we stand for') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="card-modern p-8 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                </div>
                <h3 class="font-display text-xl font-bold text-stone-900 mb-2">{{ __('Pure ingredients') }}</h3>
                <p class="text-stone-600 text-sm leading-relaxed">{{ __('We source the finest, natural ingredients. No shortcuts—just quality that you can taste.') }}</p>
            </div>
            <div class="card-modern p-8 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="font-display text-xl font-bold text-stone-900 mb-2">{{ __('Patient craft') }}</h3>
                <p class="text-stone-600 text-sm leading-relaxed">{{ __('Every cake is made with time and care. Traditional methods meet consistent excellence.') }}</p>
            </div>
            <div class="card-modern p-8 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                </div>
                <h3 class="font-display text-xl font-bold text-stone-900 mb-2">{{ __('Modern magic') }}</h3>
                <p class="text-stone-600 text-sm leading-relaxed">{{ __('Innovative designs and flavors that surprise and delight, while staying true to our roots.') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Mission, Vision, Values – clear editorial layout --}}
<section class="py-16 lg:py-24 section-warm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="heading-display text-3xl sm:text-4xl mb-3">{{ __('Our mission, vision & values') }}</h2>
            <p class="text-lg text-stone-600 max-w-2xl mx-auto">{{ __('What drives us every day') }}</p>
        </div>

        {{-- Three columns side by side (never stacked as rows) --}}
        <div class="flex flex-row flex-wrap gap-4 sm:gap-6 lg:gap-8">
            {{-- Column 1: Mission --}}
            <div class="flex-1 min-w-[200px] flex flex-col items-center text-center rounded-2xl border border-amber-100/80 bg-white p-4 shadow-sm hover:shadow-md hover:border-amber-200/80 transition-all duration-200 sm:p-6 lg:p-8">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 mb-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="font-display text-lg font-bold text-stone-900 mb-2">{{ __('Our mission') }}</h3>
                <p class="text-stone-600 text-sm leading-relaxed">{{ __('We aim to bring fresh, delicious cakes to every celebration.') }}</p>
            </div>

            {{-- Column 2: Vision --}}
            <div class="flex-1 min-w-[200px] flex flex-col items-center text-center rounded-2xl border border-amber-100/80 bg-white p-4 shadow-sm hover:shadow-md hover:border-amber-200/80 transition-all duration-200 sm:p-6 lg:p-8">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 mb-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
                <h3 class="font-display text-lg font-bold text-stone-900 mb-2">{{ __('Our vision') }}</h3>
                <p class="text-stone-600 text-sm leading-relaxed">{{ __('To be the trusted choice for quality cakes.') }}</p>
            </div>

            {{-- Column 3: Values --}}
            <div class="flex-1 min-w-[200px] flex flex-col items-center text-center rounded-2xl border border-amber-100/80 bg-white p-4 shadow-sm hover:shadow-md hover:border-amber-200/80 transition-all duration-200 sm:p-6 lg:p-8">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 mb-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                </div>
                <h3 class="font-display text-lg font-bold text-stone-900 mb-3">{{ __('Our values') }}</h3>
                <ul class="space-y-2 text-stone-600 text-sm text-left w-full max-w-[200px] mx-auto">
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span> {{ __('Quality ingredients') }}</li>
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span> {{ __('Friendly service') }}</li>
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span> {{ __('Transparency') }}</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- The team --}}
<section class="py-16 lg:py-24 section-light">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="heading-display text-3xl sm:text-4xl mb-3">{{ __('The team') }}</h2>
            <p class="text-lg text-stone-600 max-w-2xl mx-auto">{{ __('The people behind the cakes') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach([
                ['name' => __('Head Pastry Chef'), 'role' => __('Founder & Head Pastry Chef'), 'bio' => __('Passionate about French patisserie and bringing artisan quality to every creation.')],
                ['name' => __('Lead Baker'), 'role' => __('Lead Baker'), 'bio' => __('Decades of experience in traditional recipes and modern techniques.')],
                ['name' => __('Creative Director'), 'role' => __('Creative Director'), 'bio' => __('Designs our seasonal collections and custom cake experiences.')],
            ] as $member)
            <div class="card-modern p-6 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center text-amber-600 text-2xl font-display font-bold">{{ Str::limit($member['name'], 1) }}</div>
                <h3 class="font-display text-lg font-bold text-stone-900">{{ $member['name'] }}</h3>
                <p class="text-sm text-amber-600 font-medium mt-0.5">{{ $member['role'] }}</p>
                <p class="mt-2 text-stone-600 text-sm leading-relaxed">{{ $member['bio'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Visual + CTA block (aligned with home About section) --}}
<section class="py-16 lg:py-24 section-mesh">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div class="order-2 lg:order-1">
                <div class="aspect-square max-w-md rounded-3xl overflow-hidden shadow-xl bg-gradient-to-br from-amber-100 via-orange-100 to-amber-200 border border-amber-200/50">
                    <div class="h-full w-full flex items-center justify-center p-12">
                        <svg class="w-full h-full max-w-[200px] text-amber-600/40" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
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
            </div>
            <div class="order-1 lg:order-2">
                <h2 class="heading-display text-3xl sm:text-4xl mb-4">{{ __('Get in touch') }}</h2>
                <p class="text-lg text-stone-600 mb-8 leading-relaxed">{{ __('Have a question or want to place a custom order? We’d love to hear from you.') }}</p>
                <a href="{{ route('contact.index') }}" class="btn-primary-modern inline-flex">
                    {{ __('Contact us') }}
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

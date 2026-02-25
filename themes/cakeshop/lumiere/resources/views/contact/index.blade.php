@extends('layouts.app')

@section('title', __('Contact us') . ' - ' . (settings('site_name') ?: config('app.name')))

@section('content')
@php
    $openingHours = settings('opening_hours');
    $hoursLines = $openingHours ? array_filter(array_map('trim', preg_split('/[\r\n]+/', $openingHours))) : [];
@endphp

{{-- Breadcrumb --}}
<nav class="border-b border-stone-200/60 bg-[#f5f5f0] py-3">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2 text-sm text-stone-500">
            <a href="{{ route('home') }}" class="hover:text-stone-800 transition-colors">{{ __('Home') }}</a>
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="text-stone-800 font-medium">{{ __('Contact us') }}</span>
        </div>
    </div>
</nav>

{{-- Get in Touch: centered title + subtitle --}}
<section class="bg-[#f5f5f0] py-12 lg:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="heading-display text-4xl sm:text-5xl lg:text-6xl text-stone-900 mb-4">
            {{ __('Get in') }} <span class="font-normal italic text-[#C5A059]">{{ __('Touch') }}</span>
        </h1>
        <p class="text-lg sm:text-xl text-stone-600 max-w-2xl mx-auto leading-relaxed">
            {{ __("Whether you're planning a wedding, a birthday, or just have a question about our ingredients, we'd love to hear from you.") }}
        </p>
    </div>
</section>

{{-- Two columns: Contact Information (left) + Send a Message form (right) --}}
<section class="bg-[#f5f5f0] py-12 lg:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">
            {{-- Left: Contact Information --}}
            <div>
                <h2 class="text-lg font-bold text-stone-900 mb-6">{{ __('Contact Information') }}</h2>
                <div class="space-y-5">
                    @if(settings('admin_email'))
                    <div class="flex gap-3">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-stone-200/80 text-stone-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-0.5">{{ __('Email us') }}</p>
                            <a href="mailto:{{ settings('admin_email') }}" class="text-stone-800 hover:text-[#5A5A40] transition-colors">{{ settings('admin_email') }}</a>
                        </div>
                    </div>
                    @endif
                    @if(settings('contact'))
                    <div class="flex gap-3">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-stone-200/80 text-stone-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-0.5">{{ __('Call us') }}</p>
                            <a href="tel:{{ preg_replace('/\s+/', '', settings('contact')) }}" class="text-stone-800 hover:text-[#5A5A40] transition-colors">{{ settings('contact') }}</a>
                        </div>
                    </div>
                    @endif
                    @if(settings('address'))
                    <div class="flex gap-3">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-stone-200/80 text-stone-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-0.5">{{ __('Visit us') }}</p>
                            <p class="text-stone-800">{{ settings('address') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @if(count($hoursLines) > 0)
                <div class="mt-8">
                    <p class="text-sm font-bold text-stone-900 mb-3">{{ __('Opening Hours') }}</p>
                    <div class="space-y-2">
                        @foreach($hoursLines as $line)
                        <div class="rounded-xl border border-stone-200/80 bg-white/80 px-4 py-3 text-sm text-stone-700">{{ $line }}</div>
                        @endforeach
                    </div>
                </div>
                @elseif($openingHours)
                <div class="mt-8">
                    <p class="text-sm font-bold text-stone-900 mb-3">{{ __('Opening Hours') }}</p>
                    <div class="rounded-xl border border-stone-200/80 bg-white/80 px-4 py-3 text-sm text-stone-700 whitespace-pre-line">{{ $openingHours }}</div>
                </div>
                @endif
            </div>

            {{-- Right: Send a Message form (rounded panel, subtle shadow) --}}
            <div class="lumiere-contact-form-panel rounded-2xl border border-stone-200/80 bg-white/90 p-6 sm:p-8 shadow-[0_4px_24px_rgba(90,90,64,0.06)]">
                <h2 class="text-lg font-bold text-stone-900 mb-6">{{ __('Send a Message') }}</h2>
                <form method="post" action="{{ route('contact.store') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-stone-500">{{ __('Full Name') }}</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="{{ __('John Doe') }}" class="input-modern w-full" required />
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-stone-500">{{ __('Email Address') }}</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="john@example.com" class="input-modern w-full" required />
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="subject" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-stone-500">{{ __('Subject') }}</label>
                        <input type="text" name="subject" id="subject" value="{{ old('subject') }}" placeholder="{{ __('General Inquiry') }}" class="input-modern w-full" required />
                        @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="message" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-stone-500">{{ __('Your Message') }}</label>
                        <textarea name="message" id="message" rows="5" class="textarea-modern w-full" placeholder="{{ __('How can we help you?') }}" required>{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3.5 font-semibold text-white shadow-md transition-all hover:shadow-lg hover:brightness-105" style="background: linear-gradient(135deg, #5A5A40 0%, #4a4a35 100%);">
                        {{ __('Send Message') }}
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- Full-width map --}}
@if($googleMapIframe !== '')
<section class="bg-[#f5f5f0] pb-16 lg:pb-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-stone-200/80 shadow-[0_4px_24px_rgba(90,90,64,0.06)] aspect-video w-full min-h-[320px] bg-stone-200/60">
            {!! $googleMapIframe !!}
        </div>
    </div>
</section>
@endif

{{-- CTA + Back to home --}}
<section class="bg-[#ebebe6] py-12 lg:py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-stone-600 mb-6">{{ __('We usually reply within 24 hours. For urgent orders, give us a call.') }}</p>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-stone-700 font-semibold hover:text-[#5A5A40] transition-colors">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            {{ __('Back to home') }}
        </a>
    </div>
</section>
@endsection

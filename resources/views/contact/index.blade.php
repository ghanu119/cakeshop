@extends('layouts.app')

@section('title', __('Contact us') . ' - ' . (settings('site_name') ?: config('app.name')))

@section('content')
{{-- Breadcrumb --}}
<section class="border-b border-amber-100/80 bg-white py-4">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center flex-wrap gap-x-2 gap-y-1 text-sm">
            <a href="{{ route('home') }}" class="text-stone-500 hover:text-amber-600 transition-colors">{{ __('Home') }}</a>
            <svg class="h-4 w-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="text-stone-900 font-medium">{{ __('Contact us') }}</span>
        </nav>
    </div>
</section>

{{-- Hero --}}
<section class="section-light py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-8">
            <div class="max-w-3xl" data-aos="fade-up">
                <span class="badge-warm mb-4">{{ __('Contact') }}</span>
                <h1 class="heading-display text-4xl sm:text-5xl lg:text-6xl mb-6">{{ __('Contact us') }}</h1>
                <p class="text-xl text-stone-600 leading-relaxed">{{ __('Have a question or want to place a custom order? We’d love to hear from you.') }}</p>
            </div>
            <div class="hidden sm:flex flex-shrink-0" data-aos="fade-left">
                <div class="w-32 h-32 rounded-3xl bg-gradient-to-br from-amber-100 via-orange-100 to-amber-200 border border-amber-200/50 flex items-center justify-center shadow-lg">
                    <svg class="w-16 h-16 text-amber-600/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Main: Get in touch + Form (+ Map or decorative block) --}}
<section class="py-16 lg:py-24 section-warm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="heading-display text-3xl sm:text-4xl mb-3">{{ $googleMapIframe !== '' ? __('Get in touch & find us') : __('Get in touch') }}</h2>
            <p class="text-lg text-stone-600 max-w-2xl mx-auto">{{ $googleMapIframe !== '' ? __('Send a message or drop by—we’re here to help.') : __('Send a message and we’ll get back to you soon.') }}</p>
        </div>

        <div class="grid grid-cols-1 {{ $googleMapIframe !== '' ? 'lg:grid-cols-2' : '' }} gap-8 lg:gap-12">
            {{-- Info + Form (full width when no map) --}}
            <div class="space-y-8 {{ $googleMapIframe === '' ? 'max-w-3xl mx-auto w-full' : '' }}">
                @if(settings('address') || settings('contact') || settings('facebook_url') || settings('instagram_url') || settings('twitter_url'))
                <div class="card-modern p-6 sm:p-8" data-aos="fade-up">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-center text-white shadow-lg">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <h2 class="font-display text-xl font-bold text-stone-900">{{ __('Get in touch') }}</h2>
                    </div>
                    @if(settings('address'))
                        <div class="flex gap-3 text-stone-600 mb-3">
                            <svg class="h-5 w-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <p>{{ settings('address') }}</p>
                        </div>
                    @endif
                    @if(settings('contact'))
                        <div class="flex gap-3 text-stone-600 mb-3">
                            <svg class="h-5 w-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            <p>{{ settings('contact') }}</p>
                        </div>
                    @endif
                    @if(settings('opening_hours'))
                        <div class="flex gap-3 text-stone-600 mb-3">
                            <svg class="h-5 w-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>
                                <p class="font-medium text-stone-700">{{ __('Opening hours') }}</p>
                                <p class="mt-0.5 whitespace-pre-line">{{ settings('opening_hours') }}</p>
                            </div>
                        </div>
                    @endif
                    @if(settings('facebook_url') || settings('instagram_url') || settings('twitter_url'))
                        <div class="flex gap-4 mt-4 pt-4 border-t border-amber-100/80">
                            @if(settings('facebook_url'))<a href="{{ settings('facebook_url') }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-100 text-amber-600 hover:bg-amber-200 transition-colors" aria-label="{{ __('Facebook') }}"><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>@endif
                            @if(settings('instagram_url'))<a href="{{ settings('instagram_url') }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-100 text-amber-600 hover:bg-amber-200 transition-colors" aria-label="{{ __('Instagram') }}"><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>@endif
                            @if(settings('twitter_url'))<a href="{{ settings('twitter_url') }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-100 text-amber-600 hover:bg-amber-200 transition-colors" aria-label="{{ __('Twitter') }}"><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>@endif
                        </div>
                    @endif
                </div>
                @endif

                <div class="card-modern p-6 sm:p-8" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-center text-white shadow-lg">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </div>
                        <h2 class="font-display text-xl font-bold text-stone-900">{{ __('Send a message') }}</h2>
                    </div>
                    <form method="post" action="{{ route('contact.store') }}" class="space-y-5">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="name" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('Name') }} *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" class="input-modern" required />
                                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="email" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('Email') }} *</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" class="input-modern" required />
                                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="phone" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('Phone') }}</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="input-modern" />
                            </div>
                            <div>
                                <label for="inquiry_type" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('Inquiry type') }}</label>
                                <select name="inquiry_type" id="inquiry_type" class="input-modern">
                                    <option value="">{{ __('General') }}</option>
                                    <option value="wedding" @selected(old('inquiry_type') === 'wedding')>{{ __('Wedding / special event') }}</option>
                                    <option value="birthday" @selected(old('inquiry_type') === 'birthday')>{{ __('Birthday') }}</option>
                                    <option value="corporate" @selected(old('inquiry_type') === 'corporate')>{{ __('Corporate') }}</option>
                                    <option value="other" @selected(old('inquiry_type') === 'other')>{{ __('Other') }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="subject" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('Subject') }} *</label>
                            <input type="text" name="subject" id="subject" value="{{ old('subject') }}" class="input-modern" required />
                            @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="message" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('Message') }} *</label>
                            <textarea name="message" id="message" rows="5" class="textarea-modern" required>{{ old('message') }}</textarea>
                            @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn-primary-modern w-full sm:w-auto">
                            {{ __('Send message') }}
                            <svg class="ml-2 h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Right: Map only when set in Admin → Settings → Contact page map --}}
            @if($googleMapIframe !== '')
            <div class="lg:sticky lg:top-8" data-aos="fade-up" data-aos-delay="150">
                <div class="card-modern p-4 sm:p-6 overflow-hidden h-full flex flex-col">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-center text-white shadow-lg">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <h2 class="font-display text-xl font-bold text-stone-900">{{ __('Find us') }}</h2>
                    </div>
                    <div class="aspect-video w-full overflow-hidden rounded-xl flex-1 min-h-[280px]">{!! $googleMapIframe !!}</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- Closing CTA --}}
<section class="py-16 lg:py-24 section-mesh">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto" data-aos="fade-up">
            <p class="text-lg text-stone-600 mb-6">{{ __('We usually reply within 24 hours. For urgent orders, give us a call.') }}</p>
            <a href="{{ route('home') }}" class="inline-flex items-center text-amber-600 font-semibold hover:text-amber-700 transition-colors">
                {{ __('Back to home') }}
                <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            </a>
        </div>
    </div>
</section>
@endsection

@extends('layouts.app')

@section('title', __('Contact us') . ' - ' . (settings('site_name') ?: config('app.name')))

@section('content')
{{-- Breadcrumb --}}
<section class="border-b border-stone-100 bg-white py-4">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center flex-wrap gap-x-2 gap-y-1 text-sm font-medium text-stone-500">
            <a href="{{ route('home') }}" class="hover:text-amber-600 transition-colors">{{ __('Home') }}</a>
            <svg class="h-4 w-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="text-stone-900">{{ __('Contact us') }}</span>
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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-8">
            <div class="max-w-3xl">
                <span class="inline-block py-1.5 px-4 rounded-full bg-amber-100 text-amber-800 text-sm font-bold tracking-wider mb-6 border border-amber-200 shadow-sm">{{ __('Contact') }}</span>
                <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-stone-900 mb-6 drop-shadow-sm leading-tight">
                    {{ __('Contact us') }}
                </h1>
                <p class="text-xl sm:text-2xl text-stone-600 leading-relaxed font-light">
                    {{ __('Have a question or want to place a custom order? We’d love to hear from you.') }}
                </p>
            </div>
            <div class="hidden sm:flex flex-shrink-0" data-aos="fade-left">
                <div class="w-32 h-32 rounded-3xl bg-gradient-to-br from-amber-100 via-orange-100 to-amber-200 border-4 border-white flex items-center justify-center shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-500">
                    <svg class="w-14 h-14 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Main: Get in touch + Form (+ Map or decorative block) --}}
<section class="py-20 lg:py-28 bg-gradient-to-b from-stone-50 via-white to-stone-50 relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-16 left-1/4 h-56 w-56 rounded-full bg-amber-100/40 blur-3xl"></div>
        <div class="absolute bottom-8 right-1/4 h-56 w-56 rounded-full bg-orange-100/40 blur-3xl"></div>
    </div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16" data-aos="fade-up">
            <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-4 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-amber-700">
                {{ __('We are here for you') }}
            </span>
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-stone-900 mb-4">{{ $googleMapIframe !== '' ? __('Get in touch & find us') : __('Get in touch') }}</h2>
            <p class="text-xl text-stone-500 max-w-2xl mx-auto font-light">{{ $googleMapIframe !== '' ? __('Send a message or drop by—we’re here to help.') : __('Send a message and we’ll get back to you soon.') }}</p>
        </div>

        @if(settings('address') || settings('contact') || settings('opening_hours') || settings('facebook_url') || settings('instagram_url') || settings('twitter_url'))
            <div class="mb-8 rounded-3xl border border-amber-100 bg-gradient-to-b from-white to-amber-50/40 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_20px_40px_rgb(217,119,6,0.1)]" data-aos="fade-up">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <div>
                        <h2 class="font-display text-2xl font-bold text-stone-900">{{ __('Company details') }}</h2>
                        <p class="text-sm text-stone-500">{{ __('Everything you need before contacting us') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @if(settings('address'))
                        <div class="rounded-2xl border border-stone-100 bg-white p-5 shadow-sm transition-all duration-300 hover:border-amber-200">
                            <p class="text-sm font-semibold text-stone-800 mb-2 flex items-center gap-2">
                                <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                                {{ __('Address') }}
                            </p>
                            <p class="text-stone-600 leading-relaxed">{{ settings('address') }}</p>
                        </div>
                    @endif

                    @if(settings('contact'))
                        <div class="rounded-2xl border border-stone-100 bg-white p-5 shadow-sm transition-all duration-300 hover:border-amber-200">
                            <p class="text-sm font-semibold text-stone-800 mb-2 flex items-center gap-2">
                                <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                {{ __('Contact') }}
                            </p>
                            <p class="text-stone-600 leading-relaxed">{{ settings('contact') }}</p>
                        </div>
                    @endif

                    @if(settings('opening_hours'))
                        <div class="rounded-2xl border border-stone-100 bg-white p-5 shadow-sm transition-all duration-300 hover:border-amber-200 md:col-span-2 xl:col-span-1">
                            <p class="text-sm font-semibold text-stone-800 mb-2 flex items-center gap-2">
                                <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ __('Opening hours') }}
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-stone-600 leading-relaxed">
                                @foreach(preg_split('/\r\n|\r|\n/', (string) settings('opening_hours')) as $hoursLine)
                                    @if(trim($hoursLine) !== '')
                                        <p class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span><span>{{ trim($hoursLine) }}</span></p>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                @if(settings('facebook_url') || settings('instagram_url') || settings('twitter_url'))
                    <div class="mt-8 flex items-center justify-end gap-3 border-t border-stone-100 pt-8">
                        @if(settings('facebook_url'))<a href="{{ settings('facebook_url') }}" target="_blank" rel="noopener noreferrer" class="group flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-200 bg-white p-3 text-amber-500 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-300 hover:bg-amber-500 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2" aria-label="{{ __('Facebook') }}"><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>@endif
                        @if(settings('instagram_url'))<a href="{{ settings('instagram_url') }}" target="_blank" rel="noopener noreferrer" class="group flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-200 bg-white p-3 text-amber-500 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-300 hover:bg-amber-500 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2" aria-label="{{ __('Instagram') }}"><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>@endif
                        @if(settings('twitter_url'))<a href="{{ settings('twitter_url') }}" target="_blank" rel="noopener noreferrer" class="group flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-200 bg-white p-3 text-amber-500 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-300 hover:bg-amber-500 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2" aria-label="{{ __('Twitter') }}"><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>@endif
                    </div>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 @if($googleMapIframe !== '') lg:grid-cols-2 @endif gap-8 lg:gap-14">
            <div class="@if($googleMapIframe === '') max-w-3xl mx-auto w-full @endif">
                {{-- Contact Form Card --}}
                <div class="bg-white/95 rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-stone-100 transition-all duration-300 hover:shadow-[0_20px_40px_rgb(217,119,6,0.1)] backdrop-blur-sm" data-aos="fade-up" data-aos-delay="100">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                            <h2 class="font-display text-2xl font-bold text-stone-900">{{ __('Send a message') }}</h2>
                        </div>
                        <form method="post" action="{{ route('contact.store') }}" class="space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="mb-2 block text-sm font-bold text-stone-700">{{ __('Name') }} *</label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="{{ __('Your full name') }}" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-stone-900 placeholder:text-stone-400 focus:border-amber-500 focus:ring-amber-500 transition-colors" required />
                                   @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                               </div>
                               <div>
                                   <label for="email" class="mb-2 block text-sm font-bold text-stone-700">{{ __('Email') }} *</label>
                                   <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="{{ __('you@example.com') }}" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-stone-900 placeholder:text-stone-400 focus:border-amber-500 focus:ring-amber-500 transition-colors" required />
                                   @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                               </div>
                           </div>
                           <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                               <div>
                                   <label for="phone" class="mb-2 block text-sm font-bold text-stone-700">{{ __('Phone') }}</label>
                                   <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="{{ __('Optional') }}" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-stone-900 placeholder:text-stone-400 focus:border-amber-500 focus:ring-amber-500 transition-colors" />
                               </div>
                               <div>
                                   <label for="inquiry_type" class="mb-2 block text-sm font-bold text-stone-700">{{ __('Inquiry type') }}</label>
                                   <select name="inquiry_type" id="inquiry_type" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-stone-900 focus:border-amber-500 focus:ring-amber-500 transition-colors">
                                       <option value="">{{ __('General') }}</option>
                                       <option value="wedding" @selected(old('inquiry_type') === 'wedding')>{{ __('Wedding / special event') }}</option>
                                       <option value="birthday" @selected(old('inquiry_type') === 'birthday')>{{ __('Birthday') }}</option>
                                       <option value="corporate" @selected(old('inquiry_type') === 'corporate')>{{ __('Corporate') }}</option>
                                       <option value="other" @selected(old('inquiry_type') === 'other')>{{ __('Other') }}</option>
                                   </select>
                               </div>
                           </div>
                           <div>
                               <label for="subject" class="mb-2 block text-sm font-bold text-stone-700">{{ __('Subject') }} *</label>
                               <input type="text" name="subject" id="subject" value="{{ old('subject') }}" placeholder="{{ __('How can we help?') }}" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-stone-900 placeholder:text-stone-400 focus:border-amber-500 focus:ring-amber-500 transition-colors" required />
                               @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                           </div>
                           <div>
                               <label for="message" class="mb-2 block text-sm font-bold text-stone-700">{{ __('Message') }} *</label>
                               <textarea name="message" id="message" rows="5" placeholder="{{ __('Tell us your event date, flavor ideas, guest count, and any special requests...') }}" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-stone-900 placeholder:text-stone-400 focus:border-amber-500 focus:ring-amber-500 transition-colors" required>{{ old('message') }}</textarea>
                               @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                           </div>
                           <button type="submit" class="group inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold text-lg rounded-full shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 w-full sm:w-auto">
                               {{ __('Send message') }}
                               <svg class="ml-2 h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                           </button>
                       </form>
                </div>
            </div>

            {{-- Right: Map only when set in Admin -> Settings -> Contact page map --}}
            @if($googleMapIframe !== '')
                <div class="lg:sticky lg:top-28 h-fit" data-aos="fade-up" data-aos-delay="150">
                    <div class="bg-white/95 rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-stone-100 transition-all duration-300 hover:shadow-[0_20px_40px_rgb(217,119,6,0.1)] flex flex-col h-full backdrop-blur-sm">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <h2 class="font-display text-2xl font-bold text-stone-900">{{ __('Find us') }}</h2>
                        </div>

                        <div class="w-full flex-1 rounded-2xl overflow-hidden border-4 border-stone-50 shadow-sm relative min-h-[400px] lg:min-h-[560px]">
                            <div class="absolute inset-0">
                                {!! str_replace('<iframe ', '<iframe class="w-full h-full absolute inset-0" style="border:0; width:100% !important; height:100% !important;" ', $googleMapIframe) !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
       </div>
   </section>
   
   {{-- Closing CTA --}}
  <section class="py-20 lg:py-28 bg-white relative overflow-hidden">
       <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
           <div class="max-w-3xl mx-auto rounded-3xl border border-amber-100 bg-gradient-to-br from-amber-50 via-white to-orange-50 p-8 sm:p-10 text-center shadow-[0_10px_40px_rgb(0,0,0,0.05)]" data-aos="fade-up">
               <h3 class="font-display text-3xl sm:text-4xl font-bold text-stone-900 mb-3">{{ __('Let’s create something sweet together') }}</h3>
               <p class="text-lg sm:text-xl text-stone-600 mb-8 font-light">{{ __('We usually reply within 24 hours. For urgent orders, give us a call.') }}</p>
               <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                   @if(settings('contact'))
                       <a href="tel:{{ preg_replace('/\s+/', '', settings('contact')) }}" class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-amber-200 bg-white px-6 py-3 text-sm font-semibold text-stone-700 hover:border-amber-400 hover:text-amber-700 transition-colors">
                           {{ __('Call now') }}
                       </a>
                   @endif
                   <a href="{{ route('home') }}" class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-3 text-sm font-semibold text-white shadow-md hover:shadow-lg transition-all">
                       {{ __('Back to home') }}
                   </a>
               </div>
           </div>
       </div>
   </section>
   @endsection
   
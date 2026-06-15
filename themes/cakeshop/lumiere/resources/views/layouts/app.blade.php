<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', settings('site_name') ?: config('app.name'))</title>
        @if(header_icon_url())
        <link rel="icon" href="{{ header_icon_url() }}">
        @endif
        @stack('meta')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700,400italic,600italic|inter:400,500,600,700&display=swap" rel="stylesheet" />

        @stack('styles')
        {!! theme_style('css/app.css') !!}
        @vite(['resources/js/app.js'])
        <x-app-messages-script />
    </head>
    <body class="min-h-screen antialiased text-stone-800 overflow-x-hidden bg-[#f5f5f0]">
        <header class="lumiere-header fixed top-0 left-0 right-0 z-[100] w-full bg-[#f5f5f0] backdrop-blur-md shadow-md border-b border-[rgba(90,90,64,0.2)]">
            <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between">
                    <div class="flex-shrink-0">
                        <a href="{{ route('home') }}" class="flex items-center space-x-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-lg focus-visible:ring-[#5A5A40]/30">
                            @if(header_icon_url())
                                <img src="{{ header_icon_url() }}" alt="" class="h-10 w-10 rounded-full object-contain shadow-md" />
                            @else
                                <div class="logo-accent flex h-10 w-10 items-center justify-center rounded-full text-white shadow-md">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c4-4 8-8.5 8-14a8 8 0 0 0-16 0c0 5.5 4 10 8 14z"/><path d="M12 8v6"/><path d="M9 11h6"/></svg>
                                </div>
                            @endif
                            <span class="text-xl heading-display text-stone-800">{{ settings('site_name') ?: config('app.name') }}</span>
                        </a>
                    </div>

                    <div class="header-nav hidden lg:flex lg:items-center lg:gap-1">
                        <a href="{{ route('home') }}" class="nav-link rounded-lg px-3 py-2 text-base font-medium text-stone-600 transition-colors duration-200 {{ request()->routeIs('home') ? 'nav-link-active font-semibold' : '' }}">{{ __('Home') }}</a>
                        <a href="{{ route('products.index') }}" class="nav-link rounded-lg px-3 py-2 text-base font-medium text-stone-600 transition-colors duration-200 {{ request()->routeIs('products.*') ? 'nav-link-active font-semibold' : '' }}">{{ __('Shop') }}</a>
                        <a href="{{ route('about') }}" class="nav-link rounded-lg px-3 py-2 text-base font-medium text-stone-600 transition-colors duration-200 {{ request()->routeIs('about') ? 'nav-link-active font-semibold' : '' }}">{{ __('About Us') }}</a>
                        <a href="{{ route('contact.index') }}" class="nav-link rounded-lg px-3 py-2 text-base font-medium text-stone-600 transition-colors duration-200 {{ request()->routeIs('contact.*') ? 'nav-link-active font-semibold' : '' }}">{{ __('Contact') }}</a>
                        @include('partials._account-nav')
                    </div>

                    <button type="button" class="lg:hidden inline-flex items-center justify-center rounded-lg p-2.5 text-stone-700 hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-offset-2" style="--tw-ring-color: rgba(90,90,64,0.25);" id="mobile-menu-button" aria-expanded="false">
                        <span class="sr-only">{{ __('Open main menu') }}</span>
                        <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="menu-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="close-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="hidden lg:hidden" id="mobile-menu">
                    <div class="space-y-1 px-2 pb-3 pt-2">
                        <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2.5 text-base font-medium transition-colors {{ request()->routeIs('home') ? 'nav-link-active font-semibold' : 'text-stone-700 hover:bg-stone-100' }}">{{ __('Home') }}</a>
                        <a href="{{ route('products.index') }}" class="block rounded-lg px-3 py-2.5 text-base font-medium transition-colors {{ request()->routeIs('products.*') ? 'nav-link-active font-semibold' : 'text-stone-700 hover:bg-stone-100' }}">{{ __('Shop') }}</a>
                        <a href="{{ route('about') }}" class="block rounded-lg px-3 py-2.5 text-base font-medium transition-colors {{ request()->routeIs('about') ? 'nav-link-active font-semibold' : 'text-stone-700 hover:bg-stone-100' }}">{{ __('About Us') }}</a>
                        <a href="{{ route('contact.index') }}" class="block rounded-lg px-3 py-2.5 text-base font-medium transition-colors {{ request()->routeIs('contact.*') ? 'nav-link-active font-semibold' : 'text-stone-700 hover:bg-stone-100' }}">{{ __('Contact') }}</a>
                    </div>
                </div>
            </nav>
        </header>

        <script>
            document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
                const menu = document.getElementById('mobile-menu');
                const menuIcon = document.getElementById('menu-icon');
                const closeIcon = document.getElementById('close-icon');
                const isHidden = menu.classList.contains('hidden');
                menu.classList.toggle('hidden');
                if (isHidden) {
                    menuIcon.classList.add('hidden');
                    closeIcon.classList.remove('hidden');
                    this.setAttribute('aria-expanded', 'true');
                } else {
                    menuIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                    this.setAttribute('aria-expanded', 'false');
                }
            });
        </script>

        @include('partials._impersonation-banner')

        <main class="{{ app(\App\Services\CustomerContext::class)->isImpersonating() ? 'pt-32' : 'pt-20' }} min-h-[calc(100vh-5rem)]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-flash-messages class="py-3" />
            </div>
            @yield('content')
        </main>

        <footer class="mt-auto text-stone-300 border-t">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                    <div class="lg:col-span-1">
                        <div class="flex items-center space-x-2 mb-4">
                            @if(header_icon_url())
                                <img src="{{ header_icon_url() }}" alt="" class="h-10 w-10 rounded-full object-contain" />
                            @else
                                <div class="logo-accent flex h-10 w-10 items-center justify-center rounded-full text-white">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22c4-4 8-8.5 8-14a8 8 0 0 0-16 0c0 5.5 4 10 8 14z"/><path d="M12 8v6"/><path d="M9 11h6"/></svg>
                                </div>
                            @endif
                            <span class="text-xl heading-display text-white">{{ settings('site_name') ?: config('app.name') }}</span>
                        </div>
                        <p class="text-stone-400 mb-4 leading-relaxed text-sm">{{ __('Fresh cakes for every occasion') }}</p>
                        @if(settings('facebook_url') || settings('instagram_url') || settings('twitter_url'))
                        <div class="flex flex-wrap gap-2">
                            @if(settings('facebook_url'))<a href="{{ settings('facebook_url') }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors" aria-label="Facebook"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>@endif
                            @if(settings('instagram_url'))<a href="{{ settings('instagram_url') }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors" aria-label="Instagram"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z"/></svg></a>@endif
                            @if(settings('twitter_url'))<a href="{{ settings('twitter_url') }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors" aria-label="Twitter"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg></a>@endif
                        </div>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg mb-6">{{ __('Quick Links') }}</h3>
                        <ul class="space-y-3">
                            <li><a href="{{ route('home') }}" class="text-stone-400 hover:text-white transition-colors flex items-center group"><svg class="h-4 w-4 mr-2 footer-accent opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>{{ __('Home') }}</a></li>
                            <li><a href="{{ route('products.index') }}" class="text-stone-400 hover:text-white transition-colors flex items-center group"><svg class="h-4 w-4 mr-2 footer-accent opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>{{ __('Shop') }}</a></li>
                            <li><a href="{{ route('about') }}" class="text-stone-400 hover:text-white transition-colors flex items-center group"><svg class="h-4 w-4 mr-2 footer-accent opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>{{ __('About Us') }}</a></li>
                            <li><a href="{{ route('contact.index') }}" class="text-stone-400 hover:text-white transition-colors flex items-center group"><svg class="h-4 w-4 mr-2 footer-accent opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>{{ __('Contact') }}</a></li>
                            <li><a href="{{ route('order.history') }}" class="text-stone-400 hover:text-white transition-colors flex items-center group"><svg class="h-4 w-4 mr-2 footer-accent opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>{{ __('Order history') }}</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg mb-6">{{ __('Information') }}</h3>
                        <ul class="space-y-3">
                            <li><a href="{{ route('terms') }}" class="text-stone-400 hover:text-white transition-colors flex items-center group"><svg class="h-4 w-4 mr-2 footer-accent opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>{{ __('Terms & Conditions') }}</a></li>
                            <li><a href="{{ route('privacy') }}" class="text-stone-400 hover:text-white transition-colors flex items-center group"><svg class="h-4 w-4 mr-2 footer-accent opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>{{ __('Privacy Policy') }}</a></li>
                            <li><a href="{{ route('cookie-policy') }}" class="text-stone-400 hover:text-white transition-colors flex items-center group"><svg class="h-4 w-4 mr-2 footer-accent opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>{{ __('Cookie Policy') }}</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg mb-6">{{ __('Contact Us') }}</h3>
                        @if(settings('address'))
                            <div class="mb-3 flex items-start">
                                <svg class="h-5 w-5 footer-accent mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <p class="text-sm text-stone-400">{{ settings('address') }}</p>
                            </div>
                        @endif
                        @if(settings('contact'))
                            <div class="flex items-center mb-3">
                                <svg class="h-5 w-5 footer-accent mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                <p class="text-sm text-stone-400">{{ settings('contact') }}</p>
                            </div>
                        @endif
                        @if(settings('admin_email'))
                            <a href="mailto:{{ settings('admin_email') }}" class="text-stone-400 hover:text-white transition-colors text-sm flex items-center">
                                <svg class="h-5 w-5 footer-accent mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                {{ settings('admin_email') }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="mt-12 pt-8 border-t border-white/10">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <p class="text-stone-400 text-sm mb-4 md:mb-0">&copy; {{ date('Y') }} {{ settings('site_name') ?: config('app.name') }}. {{ __('All rights reserved.') }}</p>
                        <div class="flex items-center space-x-6 text-sm text-stone-400"><span>{{ __('Made with') }} <span class="text-red-400">♥</span> {{ __('for cake lovers') }}</span></div>
                    </div>
                </div>
            </div>
        </footer>
        @stack('scripts')
    </body>
</html>

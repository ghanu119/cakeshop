<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $activeTheme ?? active_theme() }}">
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
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|outfit:400,500,600,700,800&display=swap" rel="stylesheet" />
        @if(($activeTheme ?? active_theme()) === 'lumiere')
        <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700,400italic,600italic|inter:400,500,600,700&display=swap" rel="stylesheet" />
        @endif

        @stack('styles')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans antialiased text-stone-800 overflow-x-hidden">
        {{-- Modern Navigation Bar --}}
        <header class="fixed top-0 left-0 right-0 z-50 w-full bg-white/90 backdrop-blur-md shadow-md border-b border-amber-100/80">
            <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between">
                    {{-- Logo --}}
                    <div class="flex-shrink-0">
                        <a href="{{ route('home') }}" class="flex items-center space-x-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 rounded-lg">
                            @if(header_icon_url())
                                <img src="{{ header_icon_url() }}" alt="" class="h-12 w-12 rounded-full object-contain shadow-lg" />
                            @else
                                <div class="logo-accent flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-lg">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                            @endif
                            <span class="text-2xl font-display font-bold text-stone-900">{{ settings('site_name') ?: config('app.name') }}</span>
                        </a>
                    </div>

                </div>
            </nav>
        </header>

        <main class="pt-20 min-h-[calc(100vh-5rem)]">
            @if (session('status'))
                <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6 lg:px-8">
                    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="mt-auto bg-gradient-to-b from-stone-900 to-stone-950 text-stone-300 border-t border-amber-500/20">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                    {{-- Company Info --}}
                    <div class="lg:col-span-1">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="logo-accent flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <span class="text-xl font-display font-bold text-white">{{ settings('site_name') ?: config('app.name') }}</span>
                        </div>
                        <p class="text-stone-400 mb-4 leading-relaxed">{{ __('Fresh cakes for every occasion') }}</p>
                        @if(settings('address'))
                            <div class="mb-3 flex items-start">
                                <svg class="h-5 w-5 footer-accent mr-2 mt-0.5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-sm text-gray-400">{{ settings('address') }}</p>
                            </div>
                        @endif
                        @if(settings('contact'))
                            <div class="flex items-center">
                                <svg class="h-5 w-5 footer-accent mr-2 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <p class="text-sm text-gray-400">{{ settings('contact') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Quick Links --}}
                    <div>
                        <h3 class="text-white font-bold text-lg mb-6">{{ __('Quick Links') }}</h3>
                        <ul class="space-y-3">
                            <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-amber-400 transition-colors flex items-center group">
                                <svg class="h-4 w-4 mr-2 footer-accent text-amber-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                {{ __('Home') }}
                            </a></li>
                            <li><a href="{{ route('products.index') }}" class="text-gray-400 hover:text-amber-400 transition-colors flex items-center group">
                                <svg class="h-4 w-4 mr-2 footer-accent text-amber-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                {{ __('Products') }}
                            </a></li>
                            <li><a href="{{ route('contact.index') }}" class="text-gray-400 hover:text-amber-400 transition-colors flex items-center group">
                                <svg class="h-4 w-4 mr-2 footer-accent text-amber-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                {{ __('Contact') }}
                            </a></li>
                            <li><a href="{{ route('about') }}" class="text-gray-400 hover:text-amber-400 transition-colors flex items-center group">
                                <svg class="h-4 w-4 mr-2 footer-accent text-amber-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                {{ __('About') }}
                            </a></li>
                            <li><a href="{{ route('order.history') }}" class="text-gray-400 hover:text-amber-400 transition-colors flex items-center group">
                                <svg class="h-4 w-4 mr-2 footer-accent text-amber-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                {{ __('Order history (by phone)') }}
                            </a></li>
                        </ul>
                    </div>

                    {{-- Legal Links --}}
                    <div>
                        <h3 class="text-white font-bold text-lg mb-6">{{ __('Legal') }}</h3>
                        <ul class="space-y-3">
                            <li><a href="{{ route('terms') }}" class="text-gray-400 hover:text-amber-400 transition-colors flex items-center group">
                                <svg class="h-4 w-4 mr-2 footer-accent text-amber-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                {{ __('Terms & Conditions') }}
                            </a></li>
                            <li><a href="{{ route('privacy') }}" class="text-gray-400 hover:text-amber-400 transition-colors flex items-center group">
                                <svg class="h-4 w-4 mr-2 footer-accent text-amber-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                {{ __('Privacy Policy') }}
                            </a></li>
                            <li><a href="{{ route('cookie-policy') }}" class="text-gray-400 hover:text-amber-400 transition-colors flex items-center group">
                                <svg class="h-4 w-4 mr-2 footer-accent text-amber-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                {{ __('Cookie Policy') }}
                            </a></li>
                        </ul>
                    </div>

                    {{-- Social Media --}}
                    @if(settings('facebook_url') || settings('instagram_url') || settings('twitter_url'))
                    <div>
                        <h3 class="text-white font-bold text-lg mb-6">{{ __('Follow Us') }}</h3>
                        <p class="text-gray-400 mb-4 text-sm">{{ __('Stay connected with us on social media') }}</p>
                        <div class="flex flex-wrap gap-3">
                            @if(settings('facebook_url'))
                                <a href="{{ settings('facebook_url') }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-lg bg-gray-800 hover:bg-amber-500 flex items-center justify-center text-gray-400 hover:text-white transition-all duration-300 hover:scale-110" aria-label="Facebook">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                            @endif
                            @if(settings('instagram_url'))
                                <a href="{{ settings('instagram_url') }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-lg bg-gray-800 hover:bg-amber-500 flex items-center justify-center text-gray-400 hover:text-white transition-all duration-300 hover:scale-110" aria-label="Instagram">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </a>
                            @endif
                            @if(settings('twitter_url'))
                                <a href="{{ settings('twitter_url') }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-lg bg-gray-800 hover:bg-amber-500 flex items-center justify-center text-gray-400 hover:text-white transition-all duration-300 hover:scale-110" aria-label="Twitter">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                <div class="mt-12 pt-8 border-t border-gray-800">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <p class="text-gray-400 text-sm mb-4 md:mb-0">
                            &copy; {{ date('Y') }} {{ settings('site_name') ?: config('app.name') }}. {{ __('All rights reserved.') }}
                        </p>
                        <div class="flex items-center space-x-6 text-sm text-gray-400">
                            <span>{{ __('Made with') }} <span class="text-red-500">♥</span> {{ __('for cake lovers') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        {{-- Prevent double form submission: disable submit button after first click --}}
        <script>
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function() {
                    var btn = form.querySelector('button[type="submit"], input[type="submit"]');
                    if (btn && !btn.disabled) {
                        btn.disabled = true;
                        if (btn.tagName === 'BUTTON' && !btn.querySelector('svg')) {
                            var text = btn.getAttribute('data-submitting-text') || '{{ __("Processing...") }}';
                            btn.textContent = text;
                        }
                    }
                });
            });
        </script>
    </body>
</html>

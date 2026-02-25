@extends('layouts.app')

@section('content')
{{-- Hero Section --}}
<section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden bg-amber-50">
    <!-- Soft background glow effect instead of SVG pattern -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-tr from-amber-200/40 to-orange-200/40 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
    
    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div data-aos="fade-right" class="text-center lg:text-left">
                <span class="inline-block py-1.5 px-4 rounded-full bg-orange-100 text-orange-800 text-sm font-bold tracking-wider mb-6 border border-orange-200 shadow-sm">
                    {{ __('Artisan Bakery') }}
                </span>
                <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-stone-900 mb-6 drop-shadow-sm leading-tight">
                    {{ __('Sweetness') }} <br/>
                    <span class="text-orange-600">{{ __('Redefined') }}</span>
                </h1>
                <p class="text-lg sm:text-xl mb-10 text-stone-700 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    {{ __('Indulge in our handcrafted cakes made with the finest ingredients. Perfect for birthdays, weddings, anniversaries, and every special moment.') }}
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="{{ route('products.index') }}" class="group inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold text-lg rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 w-full sm:w-auto">
                        {{ __('Browse Cakes') }}
                        <svg class="ml-2 h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                    <a href="{{ route('contact.index') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white border-2 border-amber-200 text-stone-700 font-bold text-lg rounded-full hover:border-amber-500 hover:text-amber-600 transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 w-full sm:w-auto shadow-sm">
                        {{ __('Contact Us') }}
                    </a>
                </div>
            </div>
            
            <div class="relative hidden lg:block" data-aos="fade-left">
                <div class="absolute inset-0 bg-gradient-to-tr from-amber-300 to-orange-200 rounded-full blur-3xl opacity-40 animate-pulse"></div>
                <div class="relative rounded-2xl overflow-hidden shadow-xl border-4 border-white transform rotate-3 hover:rotate-0 transition-transform duration-500 bg-white" style="aspect-ratio: 4/3;">
                    <img src="{{ theme_asset('images/hero-cake.jpg') }}" alt="Delicious Chocolate Cake" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover;" />
                </div>
                
                <!-- Floating Badge -->
                <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-2xl shadow-xl flex items-center gap-4 animate-bounce" style="animation-duration: 3s;">
                    <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.898 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-stone-900">{{ __('Top Rated') }}</p>
                        <p class="text-xs text-stone-500">{{ __('100% Fresh Daily') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="absolute bottom-0 left-0 right-0">
        <svg class="w-full h-auto text-white" viewBox="0 0 1440 120" fill="currentColor" preserveAspectRatio="none">
            <path d="M0,60 C240,100 480,120 720,120 C960,120 1200,100 1440,60 L1440,120 L0,120 Z"></path>
        </svg>
    </div>
</section>

{{-- Why Choose Us / Features Section --}}
<section id="why-choose-us" class="py-20 lg:py-28 bg-white relative">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16" data-aos="fade-up">
            <span class="inline-block py-1 px-3 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold tracking-wide mb-4">{{ __('Why Choose Us') }}</span>
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-stone-900 mb-4">{{ __('We are committed to delivering excellence') }}</h2>
            <p class="text-xl text-stone-500 max-w-2xl mx-auto font-light">{{ __('Every bite is crafted with passion and the finest ingredients') }}</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @forelse($features as $feature)
                <div class="group bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(217,119,6,0.1)] border border-stone-100 transition-all duration-300 hover:-translate-y-2 relative overflow-hidden" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-amber-400 to-orange-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
                    
                    <div class="w-16 h-16 mb-6 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-gradient-to-br group-hover:from-amber-500 group-hover:to-orange-500 group-hover:text-white transition-colors duration-300">
                        @if($feature->icon === 'shield-check')
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        @elseif($feature->icon === 'clock')
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @elseif($feature->icon === 'check-circle')
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" /></svg>
                        @elseif($feature->icon === 'shopping-cart')
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        @else
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </div>
                    <h3 class="text-xl font-bold text-stone-900 mb-3">{{ $feature->title }}</h3>
                    <p class="text-stone-500 leading-relaxed">{{ $feature->description }}</p>
                </div>
            @empty
                <!-- Fallback features if none exist -->
                <div class="group bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(217,119,6,0.1)] border border-stone-100 transition-all duration-300 hover:-translate-y-2 relative overflow-hidden" data-aos="fade-up">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-amber-400 to-orange-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
                    <div class="w-16 h-16 mb-6 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-gradient-to-br group-hover:from-amber-500 group-hover:to-orange-500 group-hover:text-white transition-colors duration-300">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-stone-900 mb-3">{{ __('Premium Quality') }}</h3>
                    <p class="text-stone-500 leading-relaxed">{{ __('Made with finest ingredients and traditional recipes') }}</p>
                </div>
                <!-- Add more fallbacks if needed... -->
            @endforelse
        </div>
    </div>
</section>

{{-- Top Feature Products Section --}}
<section class="py-20 lg:py-28 bg-stone-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 mb-12" data-aos="fade-up">
            <div class="text-center sm:text-left">
                <span class="inline-block py-1 px-3 rounded-full bg-amber-100 text-amber-700 text-sm font-semibold tracking-wide mb-4">{{ __('Top Feature Products') }}</span>
                <h2 class="font-display text-4xl sm:text-5xl font-bold text-stone-900 mb-2">{{ __('Most Popular Cakes') }}</h2>
                <p class="text-xl text-stone-500 font-light">{{ __('Discover our most beloved cake creations') }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-white border border-stone-200 text-stone-700 font-semibold rounded-full hover:border-amber-500 hover:text-amber-600 shadow-sm hover:shadow transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 shrink-0">
                {{ __('View All') }}
                <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @forelse($highlights->take(4) as $product)
                <div data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    @include('products._card', ['product' => $product])
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-4 flex flex-col items-center justify-center py-20 px-6 rounded-3xl bg-white shadow-sm border border-stone-100" data-aos="fade-up">
                    <div class="w-20 h-20 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 mb-6">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    <p class="text-xl font-bold text-stone-900 mb-2">{{ __('No featured products yet') }}</p>
                    <p class="text-stone-500 text-center mb-8 max-w-md">{{ __('Browse our full collection of cakes and pastries') }}</p>
                    <a href="{{ route('products.index') }}" class="btn-primary-modern rounded-full">
                        {{ __('All Products') }}
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- About Company Section --}}
<section class="py-20 lg:py-32 bg-white overflow-hidden">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="order-2 lg:order-1 relative p-4" data-aos="fade-right">
                <div class="relative rounded-2xl overflow-hidden shadow-xl border-4 border-white bg-white" style="aspect-ratio: 4/5;">
                    <img src="{{ theme_asset('images/about-baking.jpg') }}" alt="Baking Process" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover;" />
                </div>
                
                <div class="absolute -right-4 top-1/4 bg-white p-6 rounded-2xl shadow-xl hidden md:block animate-float border border-stone-100">
                    <div class="flex items-center gap-4">
                        <div class="text-4xl font-black text-amber-500">15+</div>
                        <div class="text-sm font-bold text-stone-800 leading-tight">Years of<br/>Experience</div>
                    </div>
                </div>
            </div>
            
            <div class="order-1 lg:order-2" data-aos="fade-left">
                <span class="inline-block py-1 px-3 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold tracking-wide mb-6">{{ __('About Us') }}</span>
                <h2 class="font-display text-4xl sm:text-5xl font-bold text-stone-900 mb-6 leading-tight">{{ __('Baking memories since 2008') }}</h2>
                
                <div class="space-y-6 text-lg text-stone-600 font-light mb-10">
                    <p>
                        {{ __('With years of experience in the art of baking, we bring you the finest cakes made with love and passion. Our commitment to quality and freshness ensures every bite is a delightful experience.') }}
                    </p>
                    <p>
                        {{ __('From classic flavors to innovative designs, we create cakes that make your celebrations memorable. Every cake is crafted with attention to detail and the finest ingredients.') }}
                    </p>
                </div>
                
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('about') }}" class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold rounded-xl hover:shadow-xl transition-all duration-300 shadow-lg">
                        {{ __('Read Our Story') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Customer Reviews Section --}}
@if($testimonials->isNotEmpty())
<section class="py-20 lg:py-28 bg-amber-50 relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-amber-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
    <div class="absolute top-0 right-0 w-64 h-64 bg-orange-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
    
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16" data-aos="fade-up">
            <span class="inline-block py-1 px-3 rounded-full bg-white text-amber-700 text-sm font-semibold tracking-wide mb-4 shadow-sm">{{ __('Testimonials') }}</span>
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-stone-900 mb-4">{{ __('What Our Customers Say') }}</h2>
            <p class="text-xl text-stone-600 max-w-2xl mx-auto font-light">{{ __('Real feedback from our happy customers') }}</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($testimonials as $testimonial)
                <div class="bg-white rounded-3xl p-8 shadow-sm hover:shadow-xl transition-shadow duration-300 border border-stone-100 relative" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    
                    <div class="flex items-center space-x-1 text-amber-500 mb-6">
                        @for($i = 0; $i < $testimonial->rating; $i++)
                            <svg class="h-5 w-5 flex-shrink-0 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.898 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    
                    <p class="text-stone-600 mb-8 leading-relaxed relative z-10">"{{ $testimonial->review }}"</p>
                    
                    <div class="flex items-center mt-auto">
                        <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-lg shadow-sm">
                            {{ $testimonial->initials }}
                        </div>
                        <div class="ml-4">
                            <p class="font-bold text-stone-900">{{ $testimonial->customer_name }}</p>
                            @if($testimonial->is_verified)
                                <p class="text-xs text-green-600 flex items-center mt-0.5 font-medium">
                                    <svg class="h-3.5 w-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Verified') }}
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
<section class="py-20 lg:py-32 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <span class="inline-block py-1 px-3 rounded-full bg-stone-100 text-stone-700 text-sm font-semibold tracking-wide mb-4">{{ __('Latest Products') }}</span>
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-stone-900 mb-4">{{ __('Explore Our Collection') }}</h2>
            <p class="text-xl text-stone-500 font-light max-w-2xl mx-auto">{{ __('Discover our newest cake creations fresh from the oven') }}</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            @forelse($products->take(6) as $product)
                <div data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    @include('products._card', ['product' => $product])
                </div>
            @empty
                <div class="lg:col-span-3 flex flex-col items-center justify-center py-24 px-6 rounded-3xl bg-stone-50 border border-stone-100" data-aos="fade-up">
                    <div class="w-20 h-20 rounded-full bg-white shadow-sm flex items-center justify-center text-stone-400 mb-6">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    <p class="text-xl font-bold text-stone-900 mb-2">{{ __('No products available') }}</p>
                    <p class="text-stone-500 text-center mb-8 max-w-md">{{ __('Check back soon for new cake creations—or browse our full collection.') }}</p>
                </div>
            @endforelse
        </div>
        
        <div class="text-center" data-aos="fade-up">
            <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold rounded-xl hover:shadow-xl transition-all duration-300 shadow-lg">
                {{ __('View Full Menu') }}
                <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

<style>
    /* Custom animations for the warm theme */
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }
    .animate-float {
        animation: float 4s ease-in-out infinite;
    }
    
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob {
        animation: blob 7s infinite;
    }
    .animation-delay-2000 {
        animation-delay: 2s;
    }
</style>
@endsection
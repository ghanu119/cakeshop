{{-- Static hero (Warm default; Better Buns fallback when no sliders) --}}
<section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden bg-amber-50">
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
                    <img src="{{ theme_asset('images/hero-cake.jpg') }}" alt="{{ __('Delicious Chocolate Cake') }}" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover;" />
                </div>

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

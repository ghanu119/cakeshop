@php
    $hasSlides = isset($sliderItems) && $sliderItems->isNotEmpty();
@endphp

@if($hasSlides)
<section class="home-slider-section relative overflow-hidden">
    <div class="home-slider-aspect">
        <div class="js-home-slider home-slider h-full w-full">
            @foreach($sliderItems as $slide)
                @php
                    $isExternalLink = filled($slide->link) && preg_match('/^https?:\/\//i', $slide->link);
                    $videoEmbed = $slide->getAttribute('video_embed');
                @endphp
                <div class="home-slider-slide">
                    @if($slide->isVideo() && $videoEmbed)
                        @if($videoEmbed['kind'] === 'iframe')
                            <iframe
                                src="{{ $videoEmbed['src'] }}"
                                class="home-slider-slide__video"
                                title="{{ $slide->title ?: __('Home slide video') }}"
                                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                allow="autoplay; fullscreen; picture-in-picture"
                                referrerpolicy="strict-origin-when-cross-origin"
                            ></iframe>
                        @else
                            <video
                                class="home-slider-slide__video"
                                src="{{ $videoEmbed['src'] }}"
                                autoplay
                                muted
                                loop
                                playsinline
                                preload="{{ $loop->first ? 'auto' : 'metadata' }}"
                            ></video>
                        @endif
                    @elseif($slide->isImage())
                        <img
                            src="{{ $slide->imageUrl('large') }}"
                            alt="{{ $slide->title ?: __('Home slide') }}"
                            class="home-slider-slide__image"
                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                            width="1690"
                            height="790"
                        />
                    @endif
                    @if($slide->title)
                        <div class="home-slider-slide__overlay" aria-hidden="true"></div>

                        <div class="home-slider-slide__content">
                            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
                                <h2 class="home-slider-slide__title max-w-3xl font-display text-3xl font-extrabold leading-tight tracking-tight text-white sm:text-4xl md:text-5xl lg:text-6xl">
                                    {{ $slide->title }}
                                </h2>
                            </div>
                        </div>
                    @endif

                    @if(filled($slide->link))
                        <a
                            href="{{ $slide->link }}"
                            class="home-slider-slide__link"
                            @if($isExternalLink) target="_blank" rel="noopener noreferrer" @endif
                            aria-label="{{ $slide->title ?: __('View slide') }}"
                        ></a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="pointer-events-none absolute bottom-0 left-0 right-0 z-20">
        <svg class="h-auto w-full text-white" viewBox="0 0 1440 120" fill="currentColor" preserveAspectRatio="none">
            <path d="M0,60 C240,100 480,120 720,120 C960,120 1200,100 1440,60 L1440,120 L0,120 Z"></path>
        </svg>
    </div>
</section>
@else
    @include('home._hero-static')
@endif

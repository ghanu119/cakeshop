@extends('layouts.app')

@section('title', __('Cookie policy') . ' – ' . (settings('site_name') ?: config('app.name')))

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="mb-8 text-3xl font-bold tracking-tight text-stone-900">{{ __('Cookie policy') }}</h1>

    <div class="prose prose-stone max-w-none space-y-6">
        <p class="text-stone-600">{{ __('Last updated') }}: {{ now()->format('F j, Y') }}</p>

        <section>
            <h2 class="text-xl font-semibold text-stone-900">{{ __('What are cookies') }}</h2>
            <p class="text-stone-600">{{ __('Cookies are small text files stored on your device when you visit a website. They help the site remember your preferences and keep you logged in where applicable.') }}</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-stone-900">{{ __('How we use cookies') }}</h2>
            <p class="text-stone-600">{{ __('We use essential cookies necessary for the site to function (e.g. session, security). We do not use advertising or third-party tracking cookies by default.') }}</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-stone-900">{{ __('Managing cookies') }}</h2>
            <p class="text-stone-600">{{ __('You can control or delete cookies via your browser settings. Disabling essential cookies may affect how the site works.') }}</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-stone-900">{{ __('Contact') }}</h2>
            <p class="text-stone-600">
                <a href="{{ route('contact.index') }}" class="text-stone-800 underline hover:text-stone-900">{{ __('Contact us') }}</a>
                {{ __('for questions about cookies or this policy.') }}
            </p>
        </section>

        <p class="pt-4 text-sm text-stone-500">
            <a href="{{ route('terms') }}" class="text-stone-600 hover:underline">{{ __('Terms and conditions') }}</a>
            &middot;
            <a href="{{ route('privacy') }}" class="text-stone-600 hover:underline">{{ __('Privacy policy') }}</a>
        </p>
    </div>
</div>
@endsection

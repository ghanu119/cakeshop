@extends('layouts.app')

@section('title', __('Terms and conditions'))

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="mb-8 text-3xl font-bold tracking-tight text-stone-900">{{ __('Terms and conditions') }}</h1>
    <div class="prose prose-stone max-w-none space-y-6">
        <section>
            <h2 class="text-xl font-semibold text-stone-900">{{ __('Use of service') }}</h2>
            <p class="text-stone-600">{{ __('By using this site and placing orders you agree to these terms.') }}</p>
        </section>
        <section>
            <h2 class="text-xl font-semibold text-stone-900">{{ __('Orders and payment') }}</h2>
            <p class="text-stone-600">{{ __('Orders are subject to availability. Payment as instructed after order.') }}</p>
        </section>
        <p class="pt-4 text-sm text-stone-500">
            <a href="{{ route('privacy') }}" class="text-stone-600 hover:underline">{{ __('Privacy policy') }}</a>
            <a href="{{ route('cookie-policy') }}" class="ml-2 text-stone-600 hover:underline">{{ __('Cookie policy') }}</a>
        </p>
    </div>
</div>
@endsection

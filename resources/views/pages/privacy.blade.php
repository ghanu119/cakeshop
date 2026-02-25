@extends('layouts.app')

@section('title', __('Privacy policy'))

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="mb-8 text-3xl font-bold tracking-tight text-stone-900">{{ __('Privacy policy') }}</h1>
    <div class="prose prose-stone max-w-none space-y-6">
        <p class="text-stone-600">{{ __('Last updated') }}: {{ now()->format('F j, Y') }}</p>
        <section>
            <h2 class="text-xl font-semibold text-stone-900">{{ __('Information we collect') }}</h2>
            <p class="text-stone-600">{{ __('We collect name, email, phone and message when you order or contact us.') }}</p>
        </section>
        <section>
            <h2 class="text-xl font-semibold text-stone-900">{{ __('How we use it') }}</h2>
            <p class="text-stone-600">{{ __('We use your data only for orders and communication. We do not sell it.') }}</p>
        </section>
        <p class="pt-4 text-sm text-stone-500">
            <a href="{{ route('terms') }}" class="text-stone-600 hover:underline">{{ __('Terms') }}</a>
            <a href="{{ route('cookie-policy') }}" class="ml-2 text-stone-600 hover:underline">{{ __('Cookie policy') }}</a>
        </p>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <nav class="mb-6 text-sm text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-gray-700">{{ __('Home') }}</a>
        <span class="mx-1">/</span>
        <span class="text-gray-900">{{ $category->name_en }}</span>
    </nav>
    <h1 class="mb-6 text-3xl font-bold tracking-tight text-gray-900">{{ $category->name_en }}</h1>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($products as $product)
            @include('products._card', ['product' => $product])
        @empty
            <p class="col-span-full text-gray-500">{{ __('No products in this category.') }}</p>
        @endforelse
    </div>
    @if($products->hasPages())
        <div class="mt-6">{{ $products->links() }}</div>
    @endif
</div>
@endsection

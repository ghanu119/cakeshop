@extends('layouts.admin')

@section('title', __('Products'))

@section('content')
    @php
        $tz = settings('timezone') ?? 'Asia/Kolkata';
    @endphp

    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('Products') }}</h1>
        @can('products.create')
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center rounded-lg bg-gray-800 px-4 py-2 font-medium text-white transition duration-200 hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                {{ __('Add Product') }}
            </a>
        @endcan
    </header>

    <x-card class="mb-6">
        <form method="get" action="{{ route('admin.products.index') }}" class="flex flex-wrap items-end gap-4">
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}" />
            @endif
            @if(request('direction'))
                <input type="hidden" name="direction" value="{{ request('direction') }}" />
            @endif
            <div class="min-w-[200px] flex-1">
                <label for="search" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Search by name') }}</label>
                <x-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Search…') }}" class="block w-full" />
            </div>
            <div class="w-48">
                <label for="category_id" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Category') }}</label>
                <select name="category_id" id="category_id" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <option value="">{{ __('All') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name_en }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label for="status" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                <select name="status" id="status" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <option value="">{{ __('All') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 font-medium text-white transition duration-200 hover:bg-gray-700">
                {{ __('Filter') }}
            </button>
            @if(request()->hasAny(['search', 'category_id', 'status', 'sort', 'direction']))
                <a href="{{ route('admin.products.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 transition duration-200 hover:bg-gray-50">
                    {{ __('Reset') }}
                </a>
            @endif
        </form>
    </x-card>

    @if($products->total() > 0)
        <p class="mb-3 text-sm text-gray-600">
            @if($products->hasPages())
                {{ __('Showing :from–:to of :total products', [
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                    'total' => $products->total(),
                ]) }}
            @else
                {{ trans_choice(':count product|:count products', $products->total(), ['count' => $products->total()]) }}
            @endif
        </p>
    @endif

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.sortable-th column="name_en">{{ __('Name') }}</x-table.sortable-th>
                <x-table.sortable-th column="category">{{ __('Category') }}</x-table.sortable-th>
                <x-table.sortable-th column="price">{{ __('Price') }}</x-table.sortable-th>
                <x-table.sortable-th column="status">{{ __('Status') }}</x-table.sortable-th>
                <x-table.sortable-th column="show_on_homepage">{{ __('Homepage') }}</x-table.sortable-th>
                <x-table.sortable-th column="updated_at">{{ __('Last modified') }}</x-table.sortable-th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($products as $product)
                    <x-table.row>
                        <x-table.cell>
                            <div class="font-medium">{{ $product->name_en }}</div>
                            @if($product->getFirstMediaUrl('product_images', 'thumb'))
                                <img src="{{ $product->getFirstMediaUrl('product_images', 'thumb') }}" alt="" class="mt-1 h-10 w-10 rounded object-cover" />
                            @endif
                        </x-table.cell>
                        <x-table.cell>{{ $product->category?->name_en }}</x-table.cell>
                        <x-table.cell>₹ {{ number_format($product->price, 2) }}</x-table.cell>
                        <x-table.cell>
                            <x-badge :variant="$product->isActive() ? 'success' : 'default'">{{ $product->status }}</x-badge>
                        </x-table.cell>
                        <x-table.cell>
                            @if($product->show_on_homepage)
                                <x-badge variant="info">Yes</x-badge>
                            @else
                                —
                            @endif
                        </x-table.cell>
                        <x-table.cell class="whitespace-nowrap text-sm text-gray-600">
                            {{ $product->updated_at?->setTimezone($tz)->format('d M Y H:i') ?? '—' }}
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            @can('products.update')
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-gray-600 hover:text-gray-900">{{ __('Edit') }}</a>
                                <span class="mx-2 text-gray-300">|</span>
                            @endcan
                            @can('products.delete')
                                <x-admin-delete-form
                                    :action="route('admin.products.destroy', $product)"
                                    :title="__('Delete this product?')"
                                />
                            @endcan
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="7" class="text-center text-gray-500">{{ __('No products found.') }}</x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
    </x-card>

    @if($products->hasPages())
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    @endif
@endsection

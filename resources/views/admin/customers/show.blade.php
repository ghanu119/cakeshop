@extends('layouts.admin')

@section('title', $customer->name)

@section('content')
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.customers.index', request('status') === 'deleted' ? ['status' => 'deleted'] : []) }}" class="text-sm text-gray-500 hover:text-gray-700">{{ __('← Customers') }}</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">{{ $customer->name }}</h1>
            <div class="mt-2 flex flex-wrap gap-2">
                @if($customer->trashed())
                    <x-badge variant="warning">{{ __('Deleted') }}</x-badge>
                    @if($customer->deleted_at)
                        <span class="text-sm text-gray-500">{{ __('Purged after :days days retention', ['days' => $retentionDays]) }}</span>
                    @endif
                @elseif(! $customer->email)
                    <x-badge variant="warning">{{ __('No email yet') }}</x-badge>
                @endif
                @if($customer->registeredViaLabel())
                    <x-badge variant="default">{{ $customer->registeredViaLabel() }}</x-badge>
                @endif
            </div>
        </div>
        @if(! $customer->trashed())
            <div class="flex flex-wrap gap-3">
                @can('customers.impersonate')
                    <form method="post" action="{{ route('admin.customers.impersonate', $customer) }}">
                        @csrf
                        <x-button type="submit" variant="primary">{{ __('Shop as customer') }}</x-button>
                    </form>
                @endcan
                @can('customers.delete')
                    <form method="post" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm(@json(__('Delete this customer?')));">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger">{{ __('Delete customer') }}</x-button>
                    </form>
                @endcan
            </div>
        @endif
    </header>

    <x-card class="mb-6">
        <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">{{ __('Contact') }}</h2>
        <dl class="grid gap-3 sm:grid-cols-2">
            <div><dt class="text-xs text-gray-500">{{ __('Email') }}</dt><dd class="font-medium">{{ $customer->email ?? __('No email yet') }}</dd></div>
            <div><dt class="text-xs text-gray-500">{{ __('Phone') }}</dt><dd class="font-medium">{{ $customer->phone ?? '—' }}</dd></div>
            <div class="sm:col-span-2">
                <dt class="text-xs text-gray-500">{{ __('Verification') }}</dt>
                <dd class="mt-1 flex flex-wrap gap-2">
                    @if($customer->isWhatsAppVerified())
                        <x-badge variant="success">{{ __('WhatsApp verified') }}</x-badge>
                    @endif
                    @if($customer->isEmailVerified())
                        <x-badge variant="success">{{ __('Email verified') }}</x-badge>
                    @endif
                    @unless($customer->isWhatsAppVerified() || $customer->isEmailVerified())
                        <x-badge variant="warning">{{ __('Not verified') }}</x-badge>
                    @endunless
                </dd>
            </div>
            <div><dt class="text-xs text-gray-500">{{ __('Gender') }}</dt><dd>{{ $customer->genderLabel() ?? '—' }}</dd></div>
            <div><dt class="text-xs text-gray-500">{{ __('Birthday') }}</dt><dd>{{ $customer->birth_day && $customer->birth_month ? $customer->birth_day.' / '.$customer->birth_month : '—' }}</dd></div>
            <div><dt class="text-xs text-gray-500">{{ __('Anniversary') }}</dt><dd>{{ $customer->anniversary_day && $customer->anniversary_month ? $customer->anniversary_day.' / '.$customer->anniversary_month : '—' }}</dd></div>
        </dl>
    </x-card>

    <h2 class="mb-4 text-xl font-semibold text-gray-900">{{ __('Orders') }}</h2>
    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Order') }}</x-table.th>
                <x-table.th>{{ __('Amount') }}</x-table.th>
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($orders as $order)
                    <x-table.row>
                        <x-table.cell>
                            <span class="font-mono text-sm">{{ $order->order_no }}</span>
                            <p class="text-sm text-gray-500">{{ $order->displayProductName() }}</p>
                        </x-table.cell>
                        <x-table.cell>₹ {{ number_format($order->amount, 2) }}</x-table.cell>
                        <x-table.cell>
                            <x-badge :variant="$order->payment_status === 'verified' ? 'success' : 'warning'">{{ ucfirst($order->payment_status) }}</x-badge>
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-gray-600 hover:text-gray-900">{{ __('View') }}</a>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="4" class="text-center text-gray-500">{{ __('No orders yet.') }}</x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
    </x-card>

    @if($orders->hasPages())
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $status = $status ?? 500;
        $message = $message ?? __('errors.server');
        $title = $title ?? $message;
        $isAdmin = $isAdmin ?? false;
    @endphp
    <title>{{ $status }} – {{ settings('site_name') ?: config('app.name') }}</title>
    @if(header_icon_url())
    <link rel="icon" href="{{ header_icon_url() }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body @class([
    'min-h-screen font-sans antialiased',
    'bg-gray-50 text-gray-900' => $isAdmin ?? false,
    'bg-gradient-to-b from-amber-50 to-orange-50 text-stone-800' => ! ($isAdmin ?? false),
])>
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-16">
        <div @class([
            'w-full max-w-lg rounded-2xl border p-8 text-center shadow-xl',
            'border-gray-200 bg-white' => $isAdmin ?? false,
            'border-amber-100 bg-white/90 backdrop-blur-sm' => ! ($isAdmin ?? false),
        ])>
            <p @class([
                'text-6xl font-bold tabular-nums',
                'text-indigo-600' => $isAdmin ?? false,
                'text-amber-500' => ! ($isAdmin ?? false),
            ])>{{ $status }}</p>

            <h1 class="mt-4 text-xl font-semibold text-gray-900">{{ $title }}</h1>

            <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ $message }}</p>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                @if ($isAdmin ?? false)
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                    >
                        {{ __('errors.go_dashboard') }}
                    </a>
                @else
                    <a
                        href="{{ route('home') }}"
                        class="inline-flex items-center rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:shadow-lg"
                    >
                        {{ __('errors.go_home') }}
                    </a>
                @endif

                @isset($actionUrl)
                    <a
                        href="{{ $actionUrl }}"
                        @class([
                            'inline-flex items-center rounded-lg border px-5 py-2.5 text-sm font-semibold transition',
                            'border-gray-300 text-gray-700 hover:bg-gray-50' => $isAdmin ?? false,
                            'border-amber-200 text-amber-800 hover:bg-amber-50' => ! ($isAdmin ?? false),
                        ])
                    >
                        {{ $actionLabel ?? __('errors.try_again') }}
                    </a>
                @endisset
            </div>

            @if ($status >= 500)
                <p class="mt-6 text-xs text-gray-500">{{ __('errors.contact_support') }}</p>
            @endif
        </div>
    </div>
</body>
</html>

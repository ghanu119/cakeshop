<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Admin Login') }} – {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 font-sans antialiased text-gray-900 flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
    <div class="w-full sm:max-w-md">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block text-2xl font-bold text-gray-900">{{ config('app.name') }}</a>
            <p class="mt-2 text-sm font-medium uppercase tracking-wider text-gray-500">{{ __('Admin') }}</p>
        </div>

        <div class="bg-white shadow-lg rounded-xl px-6 py-8 sm:px-10">
            <h1 class="text-xl font-bold text-gray-900 mb-6">{{ __('Admin Login') }}</h1>

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="{{ route('admin.login') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
                    <input type="password" name="password" id="password" required autocomplete="current-password"
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</label>
                </div>
                <div>
                    <button type="submit" class="w-full flex justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ __('Log in') }}
                    </button>
                </div>
            </form>

            @if (Route::has('password.request'))
                <p class="mt-4 text-center text-sm text-gray-500">
                    <a href="{{ route('password.request') }}" class="text-indigo-600 hover:text-indigo-500">{{ __('Forgot your password?') }}</a>
                </p>
            @endif
        </div>

        <p class="mt-6 text-center text-sm text-gray-500">
            <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900">{{ __('Back to site') }}</a>
        </p>
    </div>
</body>
</html>

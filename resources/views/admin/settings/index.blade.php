@extends('layouts.admin')

@section('title', __('Settings'))

@section('content')
    <header class="mb-6">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('Settings') }}</h1>
    </header>

    <form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
            <x-form-errors :show-validation-summary="true" />
        @method('PUT')

        <x-card>
            <h2 class="mb-4 text-xl font-semibold text-gray-900">{{ __('Site & Contact') }}</h2>
            <div class="space-y-4">
                <div>
                    <label for="site_name" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Site name') }}</label>
                    <x-input type="text" name="site_name" id="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" class="block w-full" />
                </div>
                <div>
                    <label for="header_icon" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Site header icon') }}</label>
                    <input type="file" name="header_icon" id="header_icon" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-gray-700 file:hover:bg-gray-200" />
                    @if($siteSetting->getFirstMediaUrl('header_icon'))
                        <p class="mt-2 text-sm text-gray-500">{{ __('Current icon') }}:</p>
                        <img src="{{ $siteSetting->getFirstMediaUrl('header_icon') }}" alt="" class="mt-1 h-10 w-10 object-contain rounded" />
                    @endif
                    <p class="mt-1 text-sm text-gray-500">{{ __('Shown in the site header next to the site name. Leave empty to use the default icon.') }}</p>
                </div>
                <div>
                    <label for="address" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Address') }}</label>
                    <textarea name="address" id="address" rows="2" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">{{ old('address', $settings['address'] ?? '') }}</textarea>
                </div>
                <div>
                    <label for="contact" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Contact (phone / WhatsApp)') }}</label>
                    <x-input type="text" name="contact" id="contact" value="{{ old('contact', $settings['contact'] ?? '') }}" class="block w-full" />
                </div>
                <div>
                    <label for="admin_email" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Admin email') }}</label>
                    <x-input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email', $settings['admin_email'] ?? '') }}" class="block w-full" placeholder="Notifications for new order & contact enquiry" />
                    <p class="mt-1 text-sm text-gray-500">{{ __('If set, new order and contact enquiry notifications are sent here.') }}</p>
                </div>
                <div>
                    <label for="theme" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Front-end theme') }}</label>
                    <select name="theme" id="theme" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        <option value="">{{ __('Use default (from config)') }}</option>
                        @foreach(themes_available() as $key => $name)
                            <option value="{{ $key }}" {{ old('theme', $settings['theme'] ?? '') == $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Theme applied to the public site. Empty = use config default.') }}</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="facebook_url" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Facebook URL') }}</label>
                        <x-input type="url" name="facebook_url" id="facebook_url" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}" class="block w-full" placeholder="https://facebook.com/..." />
                    </div>
                    <div>
                        <label for="instagram_url" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Instagram URL') }}</label>
                        <x-input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}" class="block w-full" placeholder="https://instagram.com/..." />
                    </div>
                    <div>
                        <label for="twitter_url" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Twitter / X URL') }}</label>
                        <x-input type="url" name="twitter_url" id="twitter_url" value="{{ old('twitter_url', $settings['twitter_url'] ?? '') }}" class="block w-full" placeholder="https://twitter.com/..." />
                    </div>
                </div>
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-xl font-semibold text-gray-900">{{ __('Product page note') }}</h2>
            <div class="space-y-4">
                <div>
                    <label for="product_note" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Product note / allergens text') }}</label>
                    <textarea name="product_note" id="product_note" rows="4" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" placeholder="{{ __('e.g. Allergens: Contains gluten, dairy, eggs. Contact us for dietary requirements.') }}">{{ old('product_note', $settings['product_note'] ?? '') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Shown on each product detail page in a note box (e.g. allergens, dietary info). Leave empty to hide the note.') }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-xl font-semibold text-gray-900">{{ __('Contact page') }}</h2>
            <div class="space-y-4">
                <div>
                    <label for="opening_hours" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Opening hours') }}</label>
                    <textarea name="opening_hours" id="opening_hours" rows="3" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" placeholder="e.g. Mon–Fri 9am–6pm">{{ old('opening_hours', $settings['opening_hours'] ?? '') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Shown on the contact page. Use multiple lines for each day if needed.') }}</p>
                </div>
                <div>
                    <label for="google_map_iframe" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Google Map embed iframe (HTML)') }}</label>
                    <textarea name="google_map_iframe" id="google_map_iframe" rows="4" class="block w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">{{ old('google_map_iframe', $settings['google_map_iframe'] ?? '') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">{{ __('If set, the contact page shows the map. Leave empty to hide.') }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-xl font-semibold text-gray-900">{{ __('Payment & orders') }}</h2>
            <div class="space-y-4">
                <div>
                    <label for="payment_qr" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Payment QR code image') }}</label>
                    <input type="file" name="payment_qr" id="payment_qr" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-gray-700 file:hover:bg-gray-200" />
                    @if($siteSetting->getFirstMediaUrl('payment_qr'))
                        <p class="mt-2 text-sm text-gray-500">{{ __('Current QR') }}:</p>
                        <img src="{{ $siteSetting->getFirstMediaUrl('payment_qr') }}" alt="Payment QR" class="mt-1 h-32 w-32 object-contain rounded border border-gray-200" />
                    @endif
                    <p class="mt-1 text-sm text-gray-500">{{ __('Shown on the payment page after order is placed.') }}</p>
                </div>
                <div>
                    <label for="payment_upi_id" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Payment UPI ID') }}</label>
                    <x-input type="text" name="payment_upi_id" id="payment_upi_id" value="{{ old('payment_upi_id', $settings['payment_upi_id'] ?? '') }}" class="block w-full font-mono" placeholder="merchant@upi" />
                    <p class="mt-1 text-sm text-gray-500">{{ __('UPI handle shown on order confirmation. Enables copy and mobile UPI app pay.') }}</p>
                </div>
                <div>
                    <label for="payment_instructions" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Payment instructions') }}</label>
                    <textarea name="payment_instructions" id="payment_instructions" rows="4" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">{{ old('payment_instructions', $settings['payment_instructions'] ?? '') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Shown to guest after placing order (e.g. QR, UPI ID, bank details).') }}</p>
                </div>
                <div>
                    <label for="payment_submit_instructions" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Payment submit instructions') }}</label>
                    <textarea name="payment_submit_instructions" id="payment_submit_instructions" rows="3" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">{{ old('payment_submit_instructions', $settings['payment_submit_instructions'] ?? '') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">{{ __('What the guest should send after payment (e.g. reference, amount, time, screenshot).') }}</p>
                </div>
                <div>
                    <label for="currency" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Currency') }}</label>
                    <x-input type="text" name="currency" id="currency" value="{{ old('currency', $settings['currency'] ?? 'INR') }}" class="block w-full" />
                </div>
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-xl font-semibold text-gray-900">{{ __('Timezone & order limits') }}</h2>
            <div class="space-y-4">
                <div>
                    <label for="timezone" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Timezone') }}</label>
                    <x-input type="text" name="timezone" id="timezone" value="{{ old('timezone', $settings['timezone'] ?? 'Asia/Kolkata') }}" class="block w-full" placeholder="Asia/Kolkata" />
                </div>
                <div>
                    <label for="kitchen_lead_hours" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Kitchen lead hours') }}</label>
                    <x-input type="number" name="kitchen_lead_hours" id="kitchen_lead_hours" value="{{ old('kitchen_lead_hours', $settings['kitchen_lead_hours'] ?? '') }}" min="0" class="block w-full" placeholder="e.g. 4 or empty" />
                    <p class="mt-1 text-sm text-gray-500">{{ __('Hours before delivery the order becomes visible to kitchen. Empty = only today.') }}</p>
                </div>
                <div>
                    <label for="order_max_future_days" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Order max future days') }}</label>
                    <x-input type="number" name="order_max_future_days" id="order_max_future_days" value="{{ old('order_max_future_days', $settings['order_max_future_days'] ?? 7) }}" min="1" max="90" class="block w-full" />
                    <p class="mt-1 text-sm text-gray-500">{{ __('Guest can choose delivery up to this many days ahead.') }}</p>
                </div>
                <div>
                    <label for="order_min_hours_before_delivery" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Order min hours before delivery') }}</label>
                    <x-input type="number" name="order_min_hours_before_delivery" id="order_min_hours_before_delivery" value="{{ old('order_min_hours_before_delivery', $settings['order_min_hours_before_delivery'] ?? 4) }}" min="0" max="72" class="block w-full" />
                    <p class="mt-1 text-sm text-gray-500">{{ __('Order must be placed at least this many hours before requested delivery.') }}</p>
                </div>
                <div>
                    <label for="message_on_cake_max_length" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Message on cake — max characters') }}</label>
                    <x-input type="number" name="message_on_cake_max_length" id="message_on_cake_max_length" value="{{ old('message_on_cake_max_length', $settings['message_on_cake_max_length'] ?? \App\Models\Order::MESSAGE_ON_CAKE_MAX_LENGTH) }}" min="{{ \App\Models\Order::MESSAGE_ON_CAKE_MIN_LENGTH }}" max="{{ \App\Models\Order::MESSAGE_ON_CAKE_LIMIT_MAX }}" class="block w-full" />
                    <p class="mt-1 text-sm text-gray-500">{{ __('Default limit for the optional inscription on the order form. Individual products can override this. Allowed range: :min–:max characters.', ['min' => \App\Models\Order::MESSAGE_ON_CAKE_MIN_LENGTH, 'max' => \App\Models\Order::MESSAGE_ON_CAKE_LIMIT_MAX]) }}</p>
                    @error('message_on_cake_max_length')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-card>

        @can('settings.manage')
        <x-card>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('Real-time notifications') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Configure in-app alerts for admin and kitchen staff. Pusher credentials are encrypted in the database.') }}</p>
                </div>
                @php
                    $pusherConfigured = \App\Models\Setting::isPusherConfigured();
                    $notificationsOn = ($settings['notifications_enabled'] ?? '1') === '1';
                    $pusherIdSaved = \App\Models\Setting::hasEncryptedValue('pusher_app_id');
                    $pusherKeySaved = \App\Models\Setting::hasEncryptedValue('pusher_app_key');
                    $pusherSecretSaved = \App\Models\Setting::hasEncryptedValue('pusher_app_secret');
                    $pusherClusterSaved = \App\Models\Setting::hasEncryptedValue('pusher_app_cluster');
                    $pusherCluster = \App\Models\Setting::getPusherCluster();
                @endphp
                <div class="text-right">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $notificationsOn && $pusherConfigured ? 'bg-emerald-100 text-emerald-800' : ($notificationsOn ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600') }}">
                        {{ $notificationsOn && $pusherConfigured ? __('Ready') : ($notificationsOn ? __('Setup needed') : __('Disabled')) }}
                    </span>
                    @if ($notificationsOn && $pusherConfigured)
                        <p class="mt-1 text-xs text-emerald-700">{{ __('All Pusher credentials are saved.') }}</p>
                    @elseif ($notificationsOn)
                        <p class="mt-1 text-xs text-amber-700">{{ __('Enter the four Pusher values below.') }}</p>
                    @endif
                </div>
            </div>
            <div class="space-y-4">
                <label class="flex items-center gap-3">
                    <input type="hidden" name="notifications_enabled" value="0">
                    <input type="checkbox" name="notifications_enabled" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ old('notifications_enabled', $settings['notifications_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-gray-700">{{ __('Enable in-app notifications') }}</span>
                </label>
                <label class="flex items-center gap-3">
                    <input type="hidden" name="notifications_web_push_enabled" value="0">
                    <input type="checkbox" name="notifications_web_push_enabled" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ old('notifications_web_push_enabled', $settings['notifications_web_push_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-gray-700">{{ __('Enable browser push alerts') }}</span>
                </label>
                <p class="text-xs text-gray-500">{{ __('Delivers notifications when the browser is closed. Signing keys are created automatically in the background — no extra setup required.') }}</p>

                @if ($pusherConfigured)
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        <p class="font-medium">{{ __('Your Pusher credentials are stored securely.') }}</p>
                        <p class="mt-1 text-emerald-800">{{ __('Empty fields below are normal — they stay blank so secrets are not shown again. Only type here if you want to replace a value.') }}</p>
                        <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs font-medium text-emerald-800">
                            <li>{{ $pusherIdSaved ? '✓' : '○' }} {{ __('App ID') }}</li>
                            <li>{{ $pusherKeySaved ? '✓' : '○' }} {{ __('App Key') }}</li>
                            <li>{{ $pusherSecretSaved ? '✓' : '○' }} {{ __('App Secret') }}</li>
                            <li>{{ $pusherClusterSaved ? '✓' : '○' }} {{ __('Cluster') }}: <span class="font-mono">{{ $pusherCluster }}</span></li>
                        </ul>
                    </div>
                @else
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        <p class="font-medium">{{ __('Pusher credentials required') }}</p>
                        <p class="mt-1">{{ __('Copy App ID, Key, Secret, and Cluster from your app at pusher.com, then save this page.') }}</p>
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <label for="pusher_app_id" class="text-sm font-medium text-gray-700">{{ __('Pusher App ID') }}</label>
                            <span class="text-xs font-medium {{ $pusherIdSaved ? 'text-emerald-600' : 'text-amber-600' }}">{{ $pusherIdSaved ? __('Saved') : __('Required') }}</span>
                        </div>
                        <x-input type="password" name="pusher_app_id" id="pusher_app_id" placeholder="{{ $pusherIdSaved ? __('Leave blank to keep saved value') : __('Paste App ID from pusher.com') }}" class="block w-full font-mono" autocomplete="new-password" />
                    </div>
                    <div>
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <label for="pusher_app_key" class="text-sm font-medium text-gray-700">{{ __('Pusher App Key') }}</label>
                            <span class="text-xs font-medium {{ $pusherKeySaved ? 'text-emerald-600' : 'text-amber-600' }}">{{ $pusherKeySaved ? __('Saved') : __('Required') }}</span>
                        </div>
                        <x-input type="password" name="pusher_app_key" id="pusher_app_key" placeholder="{{ $pusherKeySaved ? __('Leave blank to keep saved value') : __('Paste App Key from pusher.com') }}" class="block w-full font-mono" autocomplete="new-password" />
                    </div>
                    <div>
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <label for="pusher_app_secret" class="text-sm font-medium text-gray-700">{{ __('Pusher App Secret') }}</label>
                            <span class="text-xs font-medium {{ $pusherSecretSaved ? 'text-emerald-600' : 'text-amber-600' }}">{{ $pusherSecretSaved ? __('Saved') : __('Required') }}</span>
                        </div>
                        <x-input type="password" name="pusher_app_secret" id="pusher_app_secret" placeholder="{{ $pusherSecretSaved ? __('Leave blank to keep saved value') : __('Paste App Secret from pusher.com') }}" class="block w-full font-mono" autocomplete="new-password" />
                    </div>
                    <div>
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <label for="pusher_app_cluster" class="text-sm font-medium text-gray-700">{{ __('Pusher Cluster') }}</label>
                            <span class="text-xs font-medium {{ $pusherClusterSaved ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $pusherClusterSaved ? __('Saved') : __('Required') }}
                                @if ($pusherClusterSaved)
                                    <span class="font-mono text-gray-500">({{ $pusherCluster }})</span>
                                @endif
                            </span>
                        </div>
                        <x-input type="text" name="pusher_app_cluster" id="pusher_app_cluster" placeholder="{{ $pusherClusterSaved ? __('Leave blank to keep :cluster', ['cluster' => $pusherCluster]) : __('e.g. ap2') }}" class="block w-full font-mono" autocomplete="off" />
                    </div>
                </div>
                <button
                    type="button"
                    data-test-pusher
                    data-test-pusher-url="{{ route('admin.settings.test-pusher') }}"
                    class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                    @disabled(! $pusherConfigured)
                >
                    {{ __('Test Pusher connection') }}
                </button>
                @unless ($pusherConfigured)
                    <p class="text-xs text-gray-500">{{ __('Save your credentials first, then test the connection.') }}</p>
                @endunless
                <p class="text-xs text-gray-500">{{ __('Get free Pusher credentials at pusher.com.') }}</p>
            </div>
        </x-card>
        @endcan

        @php
            $browserPushGloballyOn = \App\Models\Setting::isWebPushEnabled();
            $browserPushFlagOn = ($settings['notifications_web_push_enabled'] ?? '0') === '1';
            $httpsDashboard = preg_replace('#^http:#i', 'https:', route('admin.dashboard'));
        @endphp
        <x-card>
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="mb-1 text-xl font-semibold text-gray-900">{{ __('Browser alerts on this device') }}</h2>
                    <p class="text-sm text-gray-500">{{ __('Tab open: Windows popup plus in-app toast. Tab minimized: Windows popup from the background service.') }}</p>
                </div>
                <span
                    data-push-device-status-badge
                    class="rounded-full px-3 py-1 text-xs font-semibold {{ $browserPushGloballyOn ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}"
                >
                    {{ $browserPushGloballyOn ? __('Setup needed on this device') : __('Disabled in settings') }}
                </span>
            </div>

            @if ($browserPushGloballyOn)
                <div class="space-y-4">
                    <div class="rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-950">
                        <p class="font-medium">{{ __('Use HTTPS for browser alerts') }}</p>
                        <p class="mt-1">{{ __('Open the admin at') }} <a href="{{ $httpsDashboard }}" class="font-semibold underline">{{ $httpsDashboard }}</a> {{ __('then complete the steps below.') }}</p>
                    </div>

                    <p data-push-device-status class="text-sm text-gray-700">{{ __('Checking this browser…') }}</p>

                    <div class="flex flex-wrap gap-3">
                        <button
                            type="button"
                            data-enable-push
                            class="inline-flex rounded-lg border border-indigo-200 bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                        >
                            <span data-enable-push-label>{{ __('Allow browser notifications') }}</span>
                        </button>
                        <button
                            type="button"
                            data-test-push
                            class="hidden inline-flex rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            {{ __('Send test alert') }}
                        </button>
                    </div>

                    <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-600">
                        <li>{{ __('Click “Allow browser notifications”.') }}</li>
                        <li>{{ __('When Chrome asks, choose Allow.') }}</li>
                        <li>{{ __('Click “Send test alert” — a Windows popup should appear immediately (not only the green toast at the bottom).') }}</li>
                    </ol>
                </div>
            @elseif ($browserPushFlagOn)
                <p class="text-sm text-amber-800">{{ __('Browser push is enabled in settings but signing keys are still being prepared. Click Save settings above once, then refresh this page.') }}</p>
            @else
                <p class="text-sm text-gray-600">
                    @can('settings.manage')
                        {{ __('Check “Enable browser push alerts” in Real-time notifications above, then click Save settings.') }}
                    @else
                        {{ __('Ask an admin to enable browser push alerts in Settings → Real-time notifications.') }}
                    @endcan
                </p>
            @endif
        </x-card>

        <x-card>
            <h2 class="mb-2 text-xl font-semibold text-gray-900">{{ __('Cake weights') }}</h2>
            <p class="mb-4 text-sm text-gray-500">{{ __('Set the weight sizes customers can pick (250 gm, 500 gm, 1 KG, etc.). You set the price for each weight on every product.') }}</p>
            <a href="{{ route('admin.cake-weights.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                {{ __('Manage cake weights') }}
            </a>
        </x-card>

        <div class="flex gap-4">
            <x-button type="submit" variant="primary">{{ __('Save settings') }}</x-button>
        </div>
    </form>
@endsection

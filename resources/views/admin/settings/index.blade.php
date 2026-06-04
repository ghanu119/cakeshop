@extends('layouts.admin')

@section('title', __('Settings'))

@section('content')
    <header class="mb-6">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('Settings') }}</h1>
    </header>

    <form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
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

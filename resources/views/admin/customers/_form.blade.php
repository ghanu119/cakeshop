<div>
    <label for="name" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Name') }} *</label>
    <x-input type="text" name="name" id="name" value="{{ old('name') }}" required class="block w-full" data-lookup-field />
    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Phone') }} *</label>
    <x-input type="text" name="phone" id="phone" value="{{ old('phone') }}" required class="block w-full" data-lookup-field />
    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
    <x-input type="email" name="email" id="email" value="{{ old('email') }}" class="block w-full" data-lookup-field />
    <p class="mt-1 text-xs text-gray-500">{{ __('Optional. Leave blank if the customer has no email — they can add one when signing up on the website.') }}</p>
    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

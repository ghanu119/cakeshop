@props(['user'])

<div>
    <label for="name" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
    <x-input type="text" name="name" id="name" value="{{ old('name', $user?->name) }}" required class="block w-full" />
    @error('name')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
    <x-input type="email" name="email" id="email" value="{{ old('email', $user?->email) }}" required class="block w-full" />
    @error('email')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Phone') }}</label>
    <x-input type="text" name="phone" id="phone" value="{{ old('phone', $user?->phone) }}" class="block w-full" />
    @error('phone')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="password" class="mb-1 block text-sm font-medium text-gray-700">
        {{ $user ? __('New Password (leave blank to keep current)') : __('Password') }}
    </label>
    <x-input type="password" name="password" id="password" class="block w-full" autocomplete="new-password" />
    @error('password')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@if($user)
    <div>
        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Confirm Password') }}</label>
        <x-input type="password" name="password_confirmation" id="password_confirmation" class="block w-full" autocomplete="new-password" />
    </div>
@else
    <div>
        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Confirm Password') }}</label>
        <x-input type="password" name="password_confirmation" id="password_confirmation" required class="block w-full" autocomplete="new-password" />
    </div>
@endif

<div>
    <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Roles') }}</label>
    <div class="space-y-2">
        @foreach($roles as $role)
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(old('roles') ? in_array($role->name, old('roles')) : ($user && $user->hasRole($role->name))) class="rounded border-gray-300 focus:ring-gray-500" />
                <span>{{ $role->name }}</span>
            </label>
        @endforeach
    </div>
    @error('roles')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

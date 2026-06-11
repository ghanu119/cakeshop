@props([
    'showValidationSummary' => false,
    'showFieldErrors' => false,
    'showSystemErrors' => false,
])

@if ($showSystemErrors && $errors->has('_form'))
    <x-form-system-error {{ $attributes }} />
@elseif (($showSystemErrors || $showFieldErrors) && $errors->has('_form'))
    <x-alert variant="error" dismissible {{ $attributes->merge(['class' => 'mb-4']) }}>
        {{ $errors->first('_form') }}
    </x-alert>
@elseif ($showFieldErrors && $errors->any())
    <x-alert variant="error" dismissible {{ $attributes->merge(['class' => 'mb-4']) }}>
        {{ $errors->first() }}
    </x-alert>
@endif

@if ($showValidationSummary && collect($errors->keys())->reject(fn (string $key) => $key === '_form')->isNotEmpty())
    <x-alert variant="error" dismissible {{ $attributes->merge(['class' => 'mb-4']) }}>
        {{ __('Please fix the errors below.') }}
    </x-alert>
@endif

@props([
    'showFormErrors' => false,
])

@if (session('status'))
    <x-alert variant="success" dismissible class="mb-6">{{ session('status') }}</x-alert>
@endif
@if ($showFormErrors && $errors->has('_form'))
    <x-alert variant="error" dismissible class="mb-6">{{ $errors->first('_form') }}</x-alert>
@endif
@if (session('error'))
    <x-alert variant="error" dismissible class="mb-6">{{ session('error') }}</x-alert>
@endif

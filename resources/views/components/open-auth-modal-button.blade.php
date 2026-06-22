<button
    type="button"
    onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'customer-auth-modal' }))"
    {{ $attributes }}
>{{ __('Sign in') }}</button>

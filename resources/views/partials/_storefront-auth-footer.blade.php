@guest('customer')
    <livewire:account.auth-modal />
@endguest

@if(request()->boolean('auth') || session('open_auth_modal'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'customer-auth-modal' }));
        });
    </script>
@endif

@livewireScripts

@if (session('status') || session('error'))
    <div
        data-admin-flash
        @if (session('status')) data-success="{{ session('status') }}" @endif
        @if (session('error')) data-error="{{ session('error') }}" @endif
        hidden
    ></div>
@endif

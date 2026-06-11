@if (session('status') || session('error') || $errors->has('_form'))
    <div
        data-admin-flash
        @if (session('status')) data-success="{{ session('status') }}" @endif
        @if (session('error')) data-error="{{ session('error') }}" @endif
        @if ($errors->has('_form')) data-error="{{ $errors->first('_form') }}" @endif
        hidden
    ></div>
@endif

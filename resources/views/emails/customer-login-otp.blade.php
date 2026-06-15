@extends('emails.layouts.branded')

@section('content')
    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#44403c;">
        {{ __('Use this code to sign in to your account:') }}
    </p>
    <p style="margin:0 0 24px;font-size:32px;font-weight:700;letter-spacing:0.2em;color:#1c1917;text-align:center;">
        {{ $code }}
    </p>
    <p style="margin:0;font-size:14px;line-height:1.6;color:#78716c;">
        {{ __('This code expires in :minutes minutes. If you did not request it, you can ignore this email.', ['minutes' => $expiryMinutes]) }}
    </p>
@endsection

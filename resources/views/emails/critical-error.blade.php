@php
    $appName = $payload['app_name'] ?? config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Critical application error') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#fafaf9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1c1917;line-height:1.6;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fafaf9;padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #f5f5f4;">
                <tr>
                    <td style="background-color:#fef2f2;padding:24px 32px;border-bottom:1px solid #fecaca;">
                        <p style="margin:0;font-size:20px;font-weight:700;color:#991b1b;">{{ __('Critical application error') }}</p>
                        <p style="margin:8px 0 0;font-size:14px;color:#7f1d1d;">{{ $appName }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px;">
                        <p style="margin:0 0 16px;font-size:14px;"><strong>{{ __('Exception') }}:</strong> {{ $payload['exception_class'] ?? '' }}</p>
                        <p style="margin:0 0 16px;font-size:14px;"><strong>{{ __('Message') }}:</strong><br>{{ $payload['message'] ?? '' }}</p>
                        <p style="margin:0 0 16px;font-size:14px;"><strong>{{ __('Location') }}:</strong> {{ $payload['file'] ?? '' }}:{{ $payload['line'] ?? '' }}</p>
                        <p style="margin:0 0 16px;font-size:14px;"><strong>{{ __('Environment') }}:</strong> {{ $payload['environment'] ?? '' }}</p>
                        <p style="margin:0 0 16px;font-size:14px;"><strong>{{ __('App URL') }}:</strong> {{ $payload['app_url'] ?? '' }}</p>
                        <p style="margin:0 0 16px;font-size:14px;"><strong>{{ __('Occurred at') }}:</strong> {{ $payload['occurred_at'] ?? '' }}</p>

                        @if(! empty($payload['request']))
                            <p style="margin:0 0 16px;font-size:14px;"><strong>{{ __('Request') }}:</strong> {{ $payload['request']['method'] ?? '' }} {{ $payload['request']['url'] ?? '' }}</p>
                            <p style="margin:0 0 16px;font-size:14px;"><strong>{{ __('IP') }}:</strong> {{ $payload['request']['ip'] ?? '' }}</p>
                            @if(! empty($payload['request']['user']))
                                <p style="margin:0 0 16px;font-size:14px;"><strong>{{ __('User') }}:</strong> {{ $payload['request']['user']['guard'] ?? '' }} #{{ $payload['request']['user']['id'] ?? '' }}</p>
                            @endif
                        @endif

                        @if(! empty($payload['trace']))
                            <p style="margin:0 0 8px;font-size:14px;"><strong>{{ __('Stack trace') }} ({{ __('truncated') }}):</strong></p>
                            <pre style="margin:0;padding:16px;background-color:#fafaf9;border:1px solid #e7e5e4;border-radius:8px;font-size:12px;white-space:pre-wrap;word-break:break-word;">{{ $payload['trace'] }}</pre>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

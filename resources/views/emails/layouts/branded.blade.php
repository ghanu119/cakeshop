@php
    $siteName = $siteName ?? site_display_name();
    $logoUrl = $logoUrl ?? branding_logo_url();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', $siteName)</title>
</head>
<body style="margin:0;padding:0;background-color:#fafaf9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1c1917;line-height:1.6;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fafaf9;padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #f5f5f4;box-shadow:0 4px 24px rgba(28,25,23,0.06);">
                <tr>
                    <td style="background:linear-gradient(135deg,#fef3c7 0%,#fffbeb 100%);padding:28px 32px;text-align:center;border-bottom:1px solid #fde68a;">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" width="64" height="64" style="display:block;margin:0 auto 12px;border-radius:12px;object-fit:contain;">
                        @endif
                        <p style="margin:0;font-size:22px;font-weight:700;color:#78350f;letter-spacing:-0.02em;">{{ $siteName }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 32px;background-color:#fafaf9;border-top:1px solid #f5f5f4;text-align:center;">
                        <p style="margin:0 0 4px;font-size:13px;color:#78716c;">{{ __('Thank you for choosing :name.', ['name' => $siteName]) }}</p>
                        <p style="margin:0;font-size:12px;color:#a8a29e;">&copy; {{ date('Y') }} {{ $siteName }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

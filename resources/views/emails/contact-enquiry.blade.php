@extends('emails.layouts.branded')

@section('title', __('Contact enquiry'))

@section('content')
<h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#1c1917;">{{ __('Contact enquiry') }}</h1>
<p style="margin:0 0 24px;font-size:15px;color:#57534e;">
    {{ __('A new message was submitted through the contact form.') }}
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fafaf9;border-radius:12px;border:1px solid #e7e5e4;margin:0 0 24px;">
    <tr>
        <td style="padding:20px;">
            <p style="margin:0 0 12px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#78716c;">{{ __('Enquiry details') }}</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#78716c;width:100px;">{{ __('Name') }}</td>
                    <td style="padding:4px 0;font-size:14px;color:#1c1917;">{{ $enquiry->name }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#78716c;">{{ __('Email') }}</td>
                    <td style="padding:4px 0;font-size:14px;color:#1c1917;"><a href="mailto:{{ $enquiry->email }}" style="color:#b45309;text-decoration:none;">{{ $enquiry->email }}</a></td>
                </tr>
                @if($enquiry->phone)
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#78716c;">{{ __('Phone') }}</td>
                    <td style="padding:4px 0;font-size:14px;color:#1c1917;">{{ $enquiry->phone }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#78716c;">{{ __('Subject') }}</td>
                    <td style="padding:4px 0;font-size:14px;color:#1c1917;">{{ $enquiry->subject }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fffbeb;border-radius:12px;border:1px solid #fde68a;margin:0 0 24px;">
    <tr>
        <td style="padding:20px;">
            <p style="margin:0 0 12px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#92400e;">{{ __('Message') }}</p>
            <p style="margin:0;font-size:14px;color:#1c1917;white-space:pre-wrap;">{{ $enquiry->message }}</p>
        </td>
    </tr>
</table>

@include('emails.partials.cta-button', [
    'url' => route('admin.contact-enquiries.index'),
    'label' => __('View in admin'),
])
@endsection

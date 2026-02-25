@component('mail::message')
# {{ __('Contact enquiry') }}

- **{{ __('Name') }}:** {{ $enquiry->name }}
- **{{ __('Email') }}:** {{ $enquiry->email }}
@if($enquiry->phone)
- **{{ __('Phone') }}:** {{ $enquiry->phone }}
@endif
- **{{ __('Subject') }}:** {{ $enquiry->subject }}

**{{ __('Message') }}:**  
{{ $enquiry->message }}

@endcomponent

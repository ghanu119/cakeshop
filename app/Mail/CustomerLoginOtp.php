<?php

namespace App\Mail;

use App\Mail\Concerns\UsesBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerLoginOtp extends Mailable
{
    use Queueable, SerializesModels, UsesBranding;

    public function __construct(
        public string $code,
        public int $expiryMinutes
    ) {}

    public function envelope(): Envelope
    {
        return $this->brandedEnvelope(__('Your sign-in code'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-login-otp',
            with: $this->brandingViewData(),
        );
    }
}

<?php

namespace App\Mail;

use App\Models\ContactEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactEnquiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactEnquiry $enquiry
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Contact enquiry') . ': ' . $this->enquiry->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-enquiry',
        );
    }
}

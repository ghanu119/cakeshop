<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CriticalErrorNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public array $payload
    ) {}

    public function envelope(): Envelope
    {
        $shortClass = (string) ($this->payload['exception_short_class'] ?? 'Error');
        $appName = (string) ($this->payload['app_name'] ?? config('app.name'));

        return new Envelope(
            subject: sprintf('[%s] Critical error: %s', $appName, $shortClass),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.critical-error',
        );
    }
}

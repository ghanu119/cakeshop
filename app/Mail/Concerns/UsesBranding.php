<?php

namespace App\Mail\Concerns;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;

trait UsesBranding
{
    /**
     * @return array{siteName: string, logoUrl: string|null}
     */
    protected function brandingViewData(): array
    {
        return [
            'siteName' => site_display_name(),
            'logoUrl' => branding_logo_url(),
        ];
    }

    protected function brandedEnvelope(string $subjectLine): Envelope
    {
        $siteName = site_display_name();

        return new Envelope(
            from: new Address(config('mail.from.address'), $siteName),
            subject: $siteName.' — '.$subjectLine,
        );
    }
}

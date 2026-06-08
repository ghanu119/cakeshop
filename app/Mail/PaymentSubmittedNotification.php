<?php

namespace App\Mail;

use App\Mail\Concerns\UsesBranding;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentSubmittedNotification extends Mailable
{
    use Queueable, SerializesModels, UsesBranding;

    public function __construct(
        public Order $order,
        public bool $isUpdate = false
    ) {}

    public function envelope(): Envelope
    {
        $prefix = $this->isUpdate
            ? __('Payment details updated')
            : __('Payment submitted');

        return $this->brandedEnvelope($prefix.' #'.$this->order->order_no);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-submitted',
            with: $this->brandingViewData(),
        );
    }
}

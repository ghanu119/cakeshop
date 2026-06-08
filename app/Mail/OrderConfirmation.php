<?php

namespace App\Mail;

use App\Mail\Concerns\UsesBranding;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels, UsesBranding;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        return $this->brandedEnvelope(__('Order confirmed').' #'.$this->order->order_no);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
            with: $this->brandingViewData(),
        );
    }
}

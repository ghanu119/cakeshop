<?php

namespace App\Mail;

use App\Mail\Concerns\UsesBranding;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated extends Mailable
{
    use Queueable, SerializesModels, UsesBranding;

    public function __construct(
        public Order $order,
        public ?string $previousStatus = null
    ) {}

    public function envelope(): Envelope
    {
        return $this->brandedEnvelope(__('Order status updated').' #'.$this->order->order_no);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status-updated',
            with: $this->brandingViewData(),
        );
    }
}

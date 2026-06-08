<?php

namespace App\Services;

use App\Jobs\SendOrderNotificationMail;
use App\Mail\NewOrderNotification;
use App\Mail\OrderConfirmation;
use App\Mail\OrderStatusUpdated;
use App\Mail\PaymentSubmittedNotification;
use App\Mail\PaymentVerifiedNotification;
use App\Models\Order;
use Illuminate\Contracts\Mail\Mailable;

class OrderNotificationService
{
    public function notifyOrderPlaced(Order $order): void
    {
        $order->loadMissing('product');

        $this->sendToCustomer($order, new OrderConfirmation($order));

        $adminEmail = settings('admin_email');
        if ($adminEmail) {
            $this->queueSafely($adminEmail, new NewOrderNotification($order));
        }
    }

    public function notifyPaymentSubmitted(Order $order, bool $isUpdate = false): void
    {
        $order->loadMissing(['product', 'media']);

        $adminEmail = settings('admin_email');
        if (! $adminEmail) {
            return;
        }

        $this->queueSafely($adminEmail, new PaymentSubmittedNotification($order, $isUpdate));
    }

    public function notifyPaymentVerified(Order $order): void
    {
        $order->loadMissing('product');

        $this->sendToCustomer($order, new PaymentVerifiedNotification($order));
    }

    public function notifyStatusUpdated(Order $order, ?string $previousStatus = null): void
    {
        if ($previousStatus !== null && $previousStatus === $order->order_status) {
            return;
        }

        $order->loadMissing('product');

        $this->sendToCustomer($order, new OrderStatusUpdated($order, $previousStatus));
    }

    private function sendToCustomer(Order $order, Mailable $mailable): void
    {
        if (! $order->guest_email) {
            return;
        }

        $this->queueSafely($order->guest_email, $mailable);
    }

    private function queueSafely(string $recipient, Mailable $mailable): void
    {
        try {
            SendOrderNotificationMail::dispatch($recipient, $mailable);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}

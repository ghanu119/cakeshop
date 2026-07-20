<?php

namespace App\Services;

use App\Jobs\SendOrderNotificationMail;
use App\Jobs\SendWhatsAppNotification;
use App\Mail\NewOrderNotification;
use App\Mail\OrderConfirmation;
use App\Mail\OrderStatusUpdated;
use App\Mail\PaymentSubmittedNotification;
use App\Mail\PaymentVerifiedNotification;
use App\Models\Order;
use App\Notifications\KitchenOrderQueuedTodayNotification;
use App\Notifications\KitchenPaymentVerifiedTodayNotification;
use App\Notifications\NewOrderAdminNotification;
use App\Notifications\OrderCompletedAdminNotification;
use Illuminate\Contracts\Mail\Mailable;

class OrderNotificationService
{
    public function __construct(
        private InAppOrderNotificationService $inAppOrderNotificationService
    ) {}

    public function notifyOrderPlaced(Order $order): void
    {
        $order->loadMissing('product');

        $this->sendToCustomer($order, new OrderConfirmation($order));

        $adminEmail = settings('admin_email');
        if ($adminEmail) {
            $this->queueSafely($adminEmail, new NewOrderNotification($order));
        }

        $this->inAppOrderNotificationService->notifyAdmins(new NewOrderAdminNotification($order));

        $this->whatsappToCustomer($order, __('Order received'));
        $this->whatsappToAdmin($order, __('New order'));
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

        $this->whatsappToCustomer($order, __('Payment verified'));

        if ($order->isDeliveryToday()) {
            $this->inAppOrderNotificationService->notifyKitchen(
                new KitchenPaymentVerifiedTodayNotification($order)
            );
        }
    }

    public function notifyStatusUpdated(Order $order, ?string $previousStatus = null): void
    {
        if ($previousStatus !== null && $previousStatus === $order->order_status) {
            return;
        }

        $order->loadMissing('product');

        $this->sendToCustomer($order, new OrderStatusUpdated($order, $previousStatus));

        $this->whatsappToCustomer($order, $order->orderStatusLabel());

        if ($order->order_status === 'completed') {
            $this->inAppOrderNotificationService->notifyAdmins(
                new OrderCompletedAdminNotification($order)
            );
        }
    }

    public function notifyKitchenOrderQueued(Order $order): void
    {
        $order->loadMissing('product');

        $this->inAppOrderNotificationService->notifyKitchen(
            new KitchenOrderQueuedTodayNotification($order)
        );
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

    private function whatsappToCustomer(Order $order, string $statusText): void
    {
        if (! whatsapp_login_enabled() || ! config('services.whatsapp.customer_order_notifications')) {
            return;
        }

        if (empty($order->guest_phone)) {
            return;
        }

        $this->dispatchWhatsApp((string) $order->guest_phone, $order, $statusText);
    }

    private function whatsappToAdmin(Order $order, string $statusText): void
    {
        if (! whatsapp_login_enabled()) {
            return;
        }

        $adminNumber = config('services.whatsapp.admin_number');

        if (empty($adminNumber)) {
            return;
        }

        $this->dispatchWhatsApp((string) $adminNumber, $order, $statusText);
    }

    private function dispatchWhatsApp(string $phone, Order $order, string $statusText): void
    {
        try {
            SendWhatsAppNotification::dispatch(
                $phone,
                (string) config('services.whatsapp.order_template', 'order_update'),
                (string) config('services.whatsapp.order_template_lang', 'en_US'),
                [
                    (string) ($order->guest_name ?: __('Customer')),
                    (string) $order->order_no,
                    $statusText,
                ],
                $order->customerOrderWhatsAppUrlSuffix(),
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}

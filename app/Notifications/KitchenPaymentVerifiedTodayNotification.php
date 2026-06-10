<?php

namespace App\Notifications;

use App\Models\Order;

class KitchenPaymentVerifiedTodayNotification extends StaffOrderNotification
{
    protected function notificationType(): string
    {
        return 'kitchen_payment_verified_today';
    }

    protected function dedupeKeyFor(Order $order): string
    {
        return "kitchen_pay_today:order:{$order->id}";
    }

    protected function highlightTarget(): string
    {
        return 'kitchen_upcoming';
    }

    protected function titleFor(Order $order): string
    {
        return __('Today\'s order — payment verified');
    }

    protected function messageFor(Order $order): string
    {
        return __('Order :no payment verified for today\'s delivery.', ['no' => $order->order_no]);
    }

    protected function urlFor(Order $order): string
    {
        return route('admin.kitchen.orders.upcoming.show', $order);
    }
}

<?php

namespace App\Notifications;

use App\Models\Order;

class NewOrderAdminNotification extends StaffOrderNotification
{
    protected function notificationType(): string
    {
        return 'new_order';
    }

    protected function dedupeKeyFor(Order $order): string
    {
        return "new_order:order:{$order->id}";
    }

    protected function highlightTarget(): string
    {
        return 'payment_review';
    }

    protected function titleFor(Order $order): string
    {
        return __('New order received');
    }

    protected function messageFor(Order $order): string
    {
        return __('Order :no was placed.', ['no' => $order->order_no]);
    }

    protected function urlFor(Order $order): string
    {
        return route('admin.orders.show', $order);
    }
}

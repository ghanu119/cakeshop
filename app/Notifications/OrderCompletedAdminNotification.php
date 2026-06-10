<?php

namespace App\Notifications;

use App\Models\Order;

class OrderCompletedAdminNotification extends StaffOrderNotification
{
    protected function notificationType(): string
    {
        return 'order_completed';
    }

    protected function dedupeKeyFor(Order $order): string
    {
        return "order_completed:order:{$order->id}";
    }

    protected function highlightTarget(): string
    {
        return 'deliveries_today';
    }

    protected function titleFor(Order $order): string
    {
        return __('Order completed');
    }

    protected function messageFor(Order $order): string
    {
        return __('Order :no was marked completed by kitchen.', ['no' => $order->order_no]);
    }

    protected function urlFor(Order $order): string
    {
        return route('admin.orders.show', $order);
    }
}

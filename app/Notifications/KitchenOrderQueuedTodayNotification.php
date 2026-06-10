<?php

namespace App\Notifications;

use App\Models\Order;

class KitchenOrderQueuedTodayNotification extends StaffOrderNotification
{
    protected function notificationType(): string
    {
        return 'kitchen_order_queued_today';
    }

    protected function dedupeKeyFor(Order $order): string
    {
        return "kitchen_queue_today:order:{$order->id}";
    }

    protected function highlightTarget(): string
    {
        return 'kitchen_today';
    }

    protected function titleFor(Order $order): string
    {
        return __('Order ready for kitchen');
    }

    protected function messageFor(Order $order): string
    {
        return __('Order :no is in today\'s kitchen queue.', ['no' => $order->order_no]);
    }

    protected function urlFor(Order $order): string
    {
        return route('admin.kitchen.orders.show', $order);
    }
}

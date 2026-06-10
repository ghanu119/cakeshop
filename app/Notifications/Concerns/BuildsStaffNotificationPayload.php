<?php

namespace App\Notifications\Concerns;

use App\Models\Order;

trait BuildsStaffNotificationPayload
{
    abstract protected function notificationType(): string;

    abstract protected function dedupeKeyFor(Order $order): string;

    abstract protected function highlightTarget(): string;

    abstract protected function titleFor(Order $order): string;

    abstract protected function messageFor(Order $order): string;

    abstract protected function urlFor(Order $order): string;

    /**
     * @return array<string, mixed>
     */
    protected function buildPayload(Order $order): array
    {
        return [
            'type' => $this->notificationType(),
            'dedupe_key' => $this->dedupeKeyFor($order),
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'title' => $this->titleFor($order),
            'message' => $this->messageFor($order),
            'url' => $this->urlFor($order),
            'highlight_target' => $this->highlightTarget(),
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Concerns\BuildsStaffNotificationPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

abstract class StaffOrderNotification extends Notification
{
    use BuildsStaffNotificationPayload, Queueable;

    public function __construct(protected Order $order) {}

    public function order(): Order
    {
        return $this->order;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->buildPayload($this->order);
    }

    public function dedupeKey(): string
    {
        return $this->dedupeKeyFor($this->order);
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastPayload(): array
    {
        return $this->buildPayload($this->order);
    }

    /**
     * @return array{title: string, body: string, url: string}
     */
    public function webPushPayload(): array
    {
        $payload = $this->buildPayload($this->order);

        return [
            'title' => $payload['title'],
            'body' => $payload['message'],
            'url' => $payload['url'],
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WebPushStaffAlertNotification extends Notification
{
    use Queueable;

    /**
     * @param  array{title: string, body: string, url: string, id?: string, type?: string|null}  $payload
     */
    public function __construct(private array $payload) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        $data = ['url' => $this->payload['url']];

        if (! empty($this->payload['id'])) {
            $data['id'] = $this->payload['id'];
        }

        if (array_key_exists('type', $this->payload) && $this->payload['type'] !== null) {
            $data['type'] = $this->payload['type'];
        }

        return (new WebPushMessage)
            ->title($this->payload['title'])
            ->body($this->payload['body'])
            ->icon('/favicon.ico')
            ->data($data)
            ->requireInteraction(true)
            ->options(['TTL' => 86400, 'urgency' => 'high']);
    }
}

<?php

namespace App\Jobs;

use App\Messaging\Contracts\MessagingGateway;
use App\Messaging\Exceptions\MessageDeliveryException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @param  array<int, string|int|float>  $bodyParams
     * @param  array<int, int>  $backoff
     */
    public function __construct(
        public string $phone,
        public string $template,
        public string $lang,
        public array $bodyParams = [],
        public ?string $dynamicUrlButtonSuffix = null,
        public array $backoff = [60, 300],
    ) {
        $this->afterCommit();
    }

    public function handle(MessagingGateway $gateway): void
    {
        if (! $gateway->isEnabled()) {
            return;
        }

        try {
            $gateway->sendTemplate(
                $this->phone,
                $this->template,
                $this->lang,
                $this->bodyParams,
                $this->dynamicUrlButtonSuffix,
            );
        } catch (MessageDeliveryException $e) {
            // Notification-side failures must never break the flow; log and move on.
            Log::warning('WhatsApp notification failed.', [
                'reason' => $e->reason,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception !== null) {
            report($exception);
        }
    }
}

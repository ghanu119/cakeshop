<?php

namespace App\Jobs;

use App\Events\StaffNotificationBroadcasted;
use App\Services\PusherSettingsResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class BroadcastStaffNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public int $userId,
        public array $payload,
    ) {
        $this->afterCommit();
    }

    public function handle(PusherSettingsResolver $resolver): void
    {
        if (! $resolver->isEnabled()) {
            return;
        }

        try {
            $resolver->applyBroadcastingConfig();
            event(new StaffNotificationBroadcasted($this->userId, $this->payload));
        } catch (Throwable $e) {
            report($e);
            throw $e;
        }
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception !== null) {
            report($exception);
        }
    }
}

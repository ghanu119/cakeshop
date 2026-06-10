<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\WebPushStaffAlertNotification;
use App\Services\StaffPushSubscriptionService;
use App\Support\StaffNotificationUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class WebPushStaffNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    /**
     * @param  array{title: string, body: string, url: string}  $payload
     */
    public function __construct(
        public int $userId,
        public array $payload,
    ) {
        $this->afterCommit();
    }

    public function handle(StaffPushSubscriptionService $staffPushSubscriptionService): void
    {
        if (! Setting::isWebPushEnabled()) {
            return;
        }

        $user = User::query()->find($this->userId);

        if ($user === null || ! $staffPushSubscriptionService->isEligibleStaff($user)) {
            if ($user !== null) {
                $staffPushSubscriptionService->purgeForUser($user);
            }

            return;
        }

        if (! $user->pushSubscriptions()->exists()) {
            return;
        }

        $payload = [
            'title' => $this->payload['title'],
            'body' => $this->payload['body'],
            'url' => StaffNotificationUrl::sanitize($this->payload['url'] ?? null),
        ];

        $subject = preg_replace('#^http:#i', 'https:', (string) (Setting::get('webpush_subject') ?: config('app.url')));

        config([
            'webpush.vapid.subject' => $subject,
            'webpush.vapid.public_key' => Setting::getWebPushPublicKey(),
            'webpush.vapid.private_key' => Setting::getWebPushPrivateKey(),
        ]);

        try {
            $user->notify(new WebPushStaffAlertNotification($payload));
        } catch (Throwable $e) {
            report($e);

            if ($this->isInvalidSubscription($e)) {
                $this->pruneExpiredSubscriptions($user);

                return;
            }

            throw $e;
        }
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception !== null) {
            report($exception);
        }
    }

    private function isInvalidSubscription(Throwable $e): bool
    {
        $message = Str::lower($e->getMessage());

        return Str::contains($message, [
            'expired',
            'invalid',
            'gone',
            'not found',
            '410',
            '404',
        ]);
    }

    private function pruneExpiredSubscriptions(User $user): void
    {
        $user->pushSubscriptions()->delete();
    }
}

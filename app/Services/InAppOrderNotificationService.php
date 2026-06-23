<?php

namespace App\Services;

use App\Events\StaffNotificationBroadcasted;
use App\Jobs\WebPushStaffNotificationJob;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Support\AuthGuards;
use App\Notifications\KitchenOrderQueuedTodayNotification;
use App\Notifications\KitchenPaymentVerifiedTodayNotification;
use App\Notifications\StaffOrderNotification;
use Illuminate\Support\Collection;
use Throwable;

class InAppOrderNotificationService
{
    public function __construct(
        private PusherSettingsResolver $pusherSettingsResolver
    ) {}
    public function notifyAdmins(StaffOrderNotification $notification): void
    {
        try {
            $this->dispatchToUsers($this->adminUsers(), $notification);
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function notifyKitchen(StaffOrderNotification $notification): void
    {
        try {
            if (! $this->orderMatchesKitchenRules($notification)) {
                return;
            }

            $this->dispatchToUsers($this->kitchenUsers(), $notification);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function orderMatchesKitchenRules(StaffOrderNotification $notification): bool
    {
        $order = $notification->order();

        return match ($notification::class) {
            KitchenPaymentVerifiedTodayNotification::class => Order::query()
                ->whereKey($order->id)
                ->kitchenTodayVisible()
                ->exists(),
            KitchenOrderQueuedTodayNotification::class => Order::query()
                ->whereKey($order->id)
                ->kitchenTodayQueue()
                ->exists(),
            default => false,
        };
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function dispatchToUsers(Collection $users, StaffOrderNotification $notification): void
    {
        if (! Setting::isNotificationsEnabled()) {
            return;
        }

        $dedupeKey = $notification->dedupeKey();

        foreach ($users as $user) {
            if ($this->alreadyNotified($user, $dedupeKey)) {
                continue;
            }

            try {
                $user->notify($notification);
            } catch (Throwable $e) {
                report($e);

                continue;
            }

            $this->broadcastLive($user, $notification);
            $this->sendWebPushLive($user, $notification);
        }
    }

    private function sendWebPushLive(User $user, StaffOrderNotification $notification): void
    {
        try {
            $payload = $this->webPushPayloadForUser($user, $notification);
            app(StaffWebPushService::class)->sendNow($user, $payload);
        } catch (Throwable $e) {
            report($e);
            WebPushStaffNotificationJob::dispatch($user->id, $this->webPushPayloadForUser($user, $notification))->afterCommit();
        }
    }

    /**
     * @return array{title: string, body: string, url: string, id?: string, type?: string|null}
     */
    private function webPushPayloadForUser(User $user, StaffOrderNotification $notification): array
    {
        $payload = $notification->webPushPayload();
        $latest = $user->notifications()->latest()->first();

        if ($latest !== null) {
            $payload['id'] = $latest->id;
            $payload['type'] = $latest->data['type'] ?? null;
        }

        return $payload;
    }

    private function broadcastLive(User $user, StaffOrderNotification $notification): void
    {
        if (! $this->pusherSettingsResolver->isEnabled()) {
            return;
        }

        try {
            $latest = $user->notifications()->latest()->first();
            $payload = $notification->broadcastPayload();

            if ($latest !== null) {
                $payload['id'] = $latest->id;
                $payload['created_at'] = $latest->created_at?->toIso8601String();
                $payload['created_human'] = __('Just now');
            }

            $this->pusherSettingsResolver->applyBroadcastingConfig();
            event(new StaffNotificationBroadcasted($user->id, $payload));
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function alreadyNotified(User $user, string $dedupeKey): bool
    {
        return $user->notifications()
            ->where('data->dedupe_key', $dedupeKey)
            ->exists();
    }

    /**
     * @return Collection<int, User>
     */
    private function adminUsers(): Collection
    {
        return $this->eligibleStaffUsers('Admin');
    }

    /**
     * @return Collection<int, User>
     */
    private function kitchenUsers(): Collection
    {
        return $this->eligibleStaffUsers('Kitchen');
    }

    /**
     * @return Collection<int, User>
     */
    private function eligibleStaffUsers(string $role): Collection
    {
        return User::role($role, AuthGuards::STAFF)
            ->whereNotNull('email_verified_at')
            ->get();
    }
}

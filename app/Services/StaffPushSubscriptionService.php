<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\UniqueConstraintViolationException;
use Minishlink\WebPush\ContentEncoding;
use NotificationChannels\WebPush\PushSubscription;

class StaffPushSubscriptionService
{
    public function assertStaffRecipient(Authenticatable $user): void
    {
        if (! $user instanceof User) {
            throw new AuthorizationException(__('You don\'t have permission to do that.'));
        }

        if (! $this->isEligibleStaff($user)) {
            throw new AuthorizationException(__('Only admin and kitchen staff can receive these notifications.'));
        }
    }

    public function isEligibleStaff(User $user): bool
    {
        if ($user->trashed()) {
            return false;
        }

        return $user->hasAnyRole(['Admin', 'Kitchen']);
    }

    public function purgeForUser(User $user): void
    {
        $user->pushSubscriptions()->delete();
    }

    public function upsertForUser(
        User $user,
        string $endpoint,
        ?string $publicKey = null,
        ?string $authToken = null,
        ContentEncoding|string|null $contentEncoding = null
    ): PushSubscription {
        if (is_string($contentEncoding)) {
            $contentEncoding = ContentEncoding::from($contentEncoding);
        }

        try {
            $subscription = PushSubscription::query()->where('endpoint', $endpoint)->first();

            if ($subscription !== null) {
                $this->fillPushSubscription($subscription, $user, $publicKey, $authToken, $contentEncoding);
                $subscription->save();

                return $subscription;
            }

            return $user->pushSubscriptions()->create([
                'endpoint' => $endpoint,
                'public_key' => $publicKey,
                'auth_token' => $authToken,
                'content_encoding' => $contentEncoding,
            ]);
        } catch (UniqueConstraintViolationException) {
            $subscription = PushSubscription::query()->where('endpoint', $endpoint)->firstOrFail();
            $this->fillPushSubscription($subscription, $user, $publicKey, $authToken, $contentEncoding);
            $subscription->save();

            return $subscription;
        }
    }

    private function fillPushSubscription(
        PushSubscription $subscription,
        User $user,
        ?string $publicKey,
        ?string $authToken,
        ?ContentEncoding $contentEncoding
    ): void {
        $subscription->subscribable_id = $user->getKey();
        $subscription->subscribable_type = $user->getMorphClass();
        $subscription->public_key = $publicKey;
        $subscription->auth_token = $authToken;
        $subscription->content_encoding = $contentEncoding;
    }
}

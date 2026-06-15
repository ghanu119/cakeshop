<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Support\PhoneNormalizer;

class OrderLinkingService
{
    public function linkGuestOrdersForUser(User $user): int
    {
        if (! $user->isCustomer()) {
            return 0;
        }

        $normalizedPhone = PhoneNormalizer::normalize($user->phone);
        $email = $user->email;

        if ($normalizedPhone === null && ($email === null || $email === '')) {
            return 0;
        }

        $query = Order::query()->whereNull('user_id');

        $query->where(function ($q) use ($normalizedPhone, $email) {
            if ($email !== null && $email !== '') {
                $q->orWhere('guest_email', $email);
            }

            if ($normalizedPhone !== null) {
                $q->orWhere('guest_phone', $normalizedPhone);
            }
        });

        return $query->update(['user_id' => $user->id]);
    }
}

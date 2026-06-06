<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderYearSequence;
use Illuminate\Support\Facades\DB;

class OrderNumberService
{
    public function assignNext(Order $order): string
    {
        return DB::transaction(function () use ($order) {
            $tz = settings('timezone') ?? 'Asia/Kolkata';
            $orderedAt = ($order->ordered_at ?? now())->timezone($tz);
            $year = (int) $orderedAt->format('Y');
            $date = $orderedAt->format('Ymd');

            OrderYearSequence::query()->insertOrIgnore([
                'year' => $year,
                'last_number' => 0,
            ]);

            $seq = OrderYearSequence::query()
                ->where('year', $year)
                ->lockForUpdate()
                ->firstOrFail();
            $seq->increment('last_number');

            return sprintf('BB-%s-%03d', $date, $seq->last_number);
        });
    }
}

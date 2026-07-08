<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * payment_status values: pending, partially_paid, verified
     * For in-store orders, payment_amount tracks cumulative cash received.
     */
    public function up(): void
    {
        DB::table('orders')
            ->where('payment_method', 'cash_on_store')
            ->where('payment_status', 'verified')
            ->whereNull('payment_amount')
            ->update([
                'payment_amount' => DB::raw('amount'),
            ]);
    }

    public function down(): void
    {
        // No rollback — backfill is data correction only.
    }
};

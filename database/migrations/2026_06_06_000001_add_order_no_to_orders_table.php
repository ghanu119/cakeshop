<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_year_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedInteger('last_number')->default(0);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_no', 20)->nullable()->after('uuid');
        });

        $this->backfillOrderNumbers();

        Schema::table('orders', function (Blueprint $table) {
            $table->unique('order_no');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY order_no VARCHAR(20) NOT NULL');
        } else {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('order_no', 20)->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_no']);
            $table->dropColumn('order_no');
        });

        Schema::dropIfExists('order_year_sequences');
    }

    private function backfillOrderNumbers(): void
    {
        $tz = $this->shopTimezone();
        $yearCounters = [];

        $orders = DB::table('orders')
            ->orderBy('ordered_at')
            ->orderBy('id')
            ->get(['id', 'ordered_at']);

        foreach ($orders as $order) {
            $orderedAt = Carbon::parse($order->ordered_at)->timezone($tz);
            $year = (int) $orderedAt->format('Y');
            $yearCounters[$year] = ($yearCounters[$year] ?? 0) + 1;
            $orderNo = sprintf('BB-%s-%03d', $orderedAt->format('Ymd'), $yearCounters[$year]);

            DB::table('orders')->where('id', $order->id)->update(['order_no' => $orderNo]);
        }

        foreach ($yearCounters as $year => $lastNumber) {
            DB::table('order_year_sequences')->insert([
                'year' => $year,
                'last_number' => $lastNumber,
            ]);
        }
    }

    private function shopTimezone(): string
    {
        $row = DB::table('settings')->where('key', 'timezone')->first();

        return $row?->value ?: 'Asia/Kolkata';
    }
};

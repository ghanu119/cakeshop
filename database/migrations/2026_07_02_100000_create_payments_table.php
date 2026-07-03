<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('payment_gateway', 30);
            $table->string('gateway_order_id');
            $table->string('gateway_payment_id')->nullable();
            $table->text('signature')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('INR');
            $table->string('status', 20)->default('pending');
            $table->json('response_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->unique(['gateway_order_id', 'payment_gateway']);
            $table->unique('gateway_payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

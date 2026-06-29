<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->date('from_date');
            $table->date('to_date');
            $table->string('discount_type', 20);
            $table->decimal('discount_amount', 12, 2);
            $table->decimal('max_discount_amount', 12, 2)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->boolean('auto_apply')->default(false);
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->string('product_scope', 20)->default('all');
            $table->string('user_scope', 20)->default('all');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('coupon_product', function (Blueprint $table) {
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unique(['coupon_id', 'product_id']);
        });

        Schema::create('coupon_category', function (Blueprint $table) {
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unique(['coupon_id', 'category_id']);
        });

        Schema::create('coupon_user', function (Blueprint $table) {
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_user');
        Schema::dropIfExists('coupon_category');
        Schema::dropIfExists('coupon_product');
        Schema::dropIfExists('coupons');
    }
};

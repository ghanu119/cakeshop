<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->string('product_name')->nullable()->after('product_variant_id');
            $table->decimal('unit_price', 12, 2)->nullable()->after('product_name');
            $table->string('variant_summary', 500)->nullable()->after('unit_price');
            $table->unsignedInteger('weight_grams')->nullable()->index()->after('variant_summary');

            $table->index('product_variant_id');
            $table->index('product_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropIndex(['weight_grams']);
            $table->dropIndex(['product_name']);
            $table->dropColumn([
                'product_variant_id',
                'product_name',
                'unit_price',
                'variant_summary',
                'weight_grams',
            ]);
        });
    }
};

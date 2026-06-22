<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 64)->nullable()->unique()->after('slug');
            $table->string('earliest_delivery_label', 100)->nullable()->after('message_on_cake_max_length');
            $table->unsignedSmallInteger('min_hours_before_delivery')->nullable()->after('earliest_delivery_label');
            $table->boolean('show_whatsapp_customize_help')->default(false)->after('min_hours_before_delivery');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'sku',
                'earliest_delivery_label',
                'min_hours_before_delivery',
                'show_whatsapp_customize_help',
            ]);
        });
    }
};

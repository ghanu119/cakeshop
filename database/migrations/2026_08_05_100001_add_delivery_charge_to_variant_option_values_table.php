<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variant_option_values', function (Blueprint $table) {
            $table->decimal('delivery_charge', 10, 2)->nullable()->after('grams');
        });
    }

    public function down(): void
    {
        Schema::table('variant_option_values', function (Blueprint $table) {
            $table->dropColumn('delivery_charge');
        });
    }
};

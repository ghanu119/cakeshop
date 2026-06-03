<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('flavor_id')->nullable()->after('weight_grams')->constrained('flavors')->nullOnDelete();
            $table->string('flavor_name')->nullable()->after('flavor_id');
            $table->string('flavor_slug')->nullable()->after('flavor_name');

            $table->index('flavor_slug');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['flavor_id']);
            $table->dropColumn(['flavor_id', 'flavor_name', 'flavor_slug']);
        });
    }
};

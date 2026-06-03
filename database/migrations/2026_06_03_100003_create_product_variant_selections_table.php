<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variant_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_option_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_option_value_id')->constrained()->cascadeOnDelete();

            $table->unique(['product_variant_id', 'variant_option_type_id'], 'pvs_variant_type_unique');
            $table->index('variant_option_value_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_selections');
    }
};

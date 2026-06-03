<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_variant_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_option_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('variant_option_type_slug', 50)->index();
            $table->foreignId('variant_option_value_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->unsignedInteger('grams')->nullable()->index();
            $table->timestamps();

            $table->index('order_id');
            $table->index(['variant_option_type_slug', 'grams']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_variant_selections');
    }
};

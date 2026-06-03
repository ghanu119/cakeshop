<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_option_type_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('grams')->nullable()->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['variant_option_type_id', 'status']);
            $table->index(['variant_option_type_id', 'grams']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_option_values');
    }
};

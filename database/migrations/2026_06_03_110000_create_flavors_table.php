<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flavors', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('slug')->unique();
            $table->string('status', 20)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('badge_color', 32)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('name_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flavors');
    }
};

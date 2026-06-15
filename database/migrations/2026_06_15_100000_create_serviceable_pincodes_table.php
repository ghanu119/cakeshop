<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serviceable_pincodes', function (Blueprint $table) {
            $table->id();
            $table->char('pincode', 6)->unique();
            $table->string('locality')->nullable();
            $table->string('city')->default('Rajkot');
            $table->string('state')->default('Gujarat');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'pincode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serviceable_pincodes');
    }
};

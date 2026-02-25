<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_hi')->nullable();
            $table->string('name_gu')->nullable();
            $table->string('slug')->unique();
            $table->text('description_en')->nullable();
            $table->text('description_hi')->nullable();
            $table->text('description_gu')->nullable();
            $table->string('short_description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('status', 20)->default('active')->index();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->boolean('show_on_homepage')->default(false)->index();
            $table->boolean('is_highlight')->default(false)->index();
            $table->boolean('is_trending')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('homepage_sort_order')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

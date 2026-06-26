<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $homeSliderId = DB::table('sliders')->insertGetId([
            'name' => 'Home Slider',
            'slug' => 'home',
            'description' => 'Homepage hero carousel for Better Buns theme',
            'is_system' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::rename('home_sliders', 'slider_items');

        Schema::table('slider_items', function (Blueprint $table) {
            $table->foreignId('slider_id')->nullable()->after('id')->constrained('sliders')->cascadeOnDelete();
            $table->string('type', 20)->default('image')->after('slider_id');
            $table->string('video_url', 2048)->nullable()->after('link');
        });

        DB::table('slider_items')->update([
            'slider_id' => $homeSliderId,
            'type' => 'image',
        ]);

        DB::table('media')
            ->where('model_type', 'App\\Models\\HomeSlider')
            ->update(['model_type' => 'App\\Models\\SliderItem']);
    }

    public function down(): void
    {
        DB::table('media')
            ->where('model_type', 'App\\Models\\SliderItem')
            ->update(['model_type' => 'App\\Models\\HomeSlider']);

        Schema::table('slider_items', function (Blueprint $table) {
            $table->dropForeign(['slider_id']);
            $table->dropColumn(['slider_id', 'type', 'video_url']);
        });

        Schema::rename('slider_items', 'home_sliders');
        Schema::dropIfExists('sliders');
    }
};

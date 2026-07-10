<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_otps', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 20)->default('email');
            $table->string('destination')->index();
            $table->string('code_hash');
            $table->string('purpose', 20)->default('login');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('consumed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['channel', 'destination']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_otps');
    }
};

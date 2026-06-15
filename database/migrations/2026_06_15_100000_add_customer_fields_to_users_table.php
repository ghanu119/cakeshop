<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->unsignedTinyInteger('birth_day')->nullable()->after('phone');
            $table->unsignedTinyInteger('birth_month')->nullable()->after('birth_day');
            $table->unsignedTinyInteger('anniversary_day')->nullable()->after('birth_month');
            $table->unsignedTinyInteger('anniversary_month')->nullable()->after('anniversary_day');
            $table->string('gender', 30)->nullable()->after('anniversary_month');
            $table->string('registered_via', 20)->nullable()->after('gender');
            $table->timestamp('email_claimed_at')->nullable()->after('registered_via');
            $table->foreignId('created_by_admin_id')->nullable()->after('email_claimed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('deletion_requested_at')->nullable()->after('created_by_admin_id');
            $table->string('deletion_reason', 30)->nullable()->after('deletion_requested_at');
            $table->timestamp('purged_at')->nullable()->after('deletion_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by_admin_id']);
            $table->dropColumn([
                'birth_day',
                'birth_month',
                'anniversary_day',
                'anniversary_month',
                'gender',
                'registered_via',
                'email_claimed_at',
                'created_by_admin_id',
                'deletion_requested_at',
                'deletion_reason',
                'purged_at',
            ]);
            $table->string('password')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
        });
    }
};

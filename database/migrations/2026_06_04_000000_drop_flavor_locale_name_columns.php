<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('flavors')) {
            return;
        }

        Schema::table('flavors', function (Blueprint $table) {
            if (Schema::hasColumn('flavors', 'name_hi')) {
                $table->dropColumn('name_hi');
            }
            if (Schema::hasColumn('flavors', 'name_gu')) {
                $table->dropColumn('name_gu');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('flavors')) {
            return;
        }

        Schema::table('flavors', function (Blueprint $table) {
            if (! Schema::hasColumn('flavors', 'name_hi')) {
                $table->string('name_hi')->nullable()->after('name_en');
            }
            if (! Schema::hasColumn('flavors', 'name_gu')) {
                $table->string('name_gu')->nullable()->after('name_hi');
            }
        });
    }
};

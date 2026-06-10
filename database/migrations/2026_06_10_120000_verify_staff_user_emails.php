<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        User::query()
            ->whereNull('email_verified_at')
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['Admin', 'Kitchen']))
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Staff verification is required for notifications; do not revert.
    }
};

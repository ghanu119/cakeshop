<?php

use App\Models\Setting;
use App\Services\WebPushVapidService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if ((string) Setting::get('notifications_enabled', '0') !== '1') {
            return;
        }

        Setting::set('notifications_web_push_enabled', '1');
        app(WebPushVapidService::class)->ensureKeysProvisioned();
        Setting::flushCache();
    }

    public function down(): void
    {
        //
    }
};

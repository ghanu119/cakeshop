<?php

namespace App\View\Composers;

use App\Models\Setting;
use App\Services\PusherSettingsResolver;
use Illuminate\View\View;
use Throwable;

class AdminNotificationComposer
{
    public function __construct(
        private PusherSettingsResolver $pusherSettingsResolver
    ) {}

    public function compose(View $view): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        try {
            $unreadCount = $user->unreadNotifications()->count();
            $unreadNotifications = $user->unreadNotifications()->latest()->limit(15)->get();
            $unreadHighlightTargets = $unreadNotifications
                ->pluck('data.highlight_target')
                ->filter()
                ->unique()
                ->values();

            $view->with([
                'unreadNotificationCount' => $unreadCount,
                'unreadNotifications' => $unreadNotifications,
                'notificationWatermark' => $unreadNotifications->first()?->created_at?->toIso8601String(),
                'unreadHighlightTargets' => $unreadHighlightTargets,
                'notificationSystemUnavailable' => false,
                'notificationsEnabled' => Setting::isNotificationsEnabled(),
                'webPushEnabled' => Setting::isWebPushEnabled(),
                'pusherFrontendConfig' => $this->pusherSettingsResolver->frontendConfig(),
            ]);
        } catch (Throwable $e) {
            report($e);

            $view->with([
                'unreadNotificationCount' => 0,
                'unreadNotifications' => collect(),
                'notificationWatermark' => null,
                'unreadHighlightTargets' => collect(),
                'notificationSystemUnavailable' => true,
                'notificationsEnabled' => false,
                'webPushEnabled' => false,
                'pusherFrontendConfig' => ['enabled' => false, 'key' => null, 'cluster' => 'mt1'],
            ]);
        }
    }
}

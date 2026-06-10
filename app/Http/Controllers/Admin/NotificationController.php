<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestroyPushSubscriptionRequest;
use App\Http\Requests\Admin\StorePushSubscriptionRequest;
use App\Http\Responses\ApiResponse;
use App\Services\StaffPushSubscriptionService;
use App\Services\StaffWebPushService;
use App\Support\StaffNotificationUrl;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use NotificationChannels\WebPush\PushSubscription;
use Throwable;

class NotificationController extends Controller
{
    public function __construct(
        private StaffPushSubscriptionService $staffPushSubscriptionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $notifications = $user->notifications()
                ->when($request->boolean('unread_only'), fn ($q) => $q->whereNull('read_at'))
                ->latest()
                ->paginate(20);

            return ApiResponse::success([
                'items' => $notifications->map(fn (DatabaseNotification $n) => $this->formatNotification($n)),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'total' => $notifications->total(),
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error(
                __('Couldn\'t refresh notifications. Showing your last saved list.'),
                500
            );
        }
    }

    public function unreadCount(Request $request): JsonResponse
    {
        try {
            return ApiResponse::success([
                'count' => $request->user()->unreadNotifications()->count(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error(
                __('Couldn\'t refresh notification count.'),
                500
            );
        }
    }

    public function since(Request $request): JsonResponse
    {
        try {
            $after = $request->query('after');
            $query = $request->user()->notifications()->latest();

            if ($after) {
                try {
                    $query->where('created_at', '>', Carbon::parse($after));
                } catch (\Throwable) {
                    // Ignore invalid cursor and return recent items.
                }
            }

            $items = $query->limit(50)->get();

            return ApiResponse::success([
                'items' => $items->map(fn (DatabaseNotification $n) => $this->formatNotification($n)),
            ]);
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error(
                __('Couldn\'t fetch new notifications. We\'ll try again shortly.'),
                500
            );
        }
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        try {
            $notification = $this->findOwnedNotification($request, $id);

            if ($notification === null) {
                return ApiResponse::error(__('That notification is no longer available.'), 404);
            }

            $notification->markAsRead();

            return ApiResponse::success([
                'notification' => $this->formatNotification($notification->fresh()),
            ], __('Notification marked as read.'));
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error(__('Couldn\'t update notification. Please try again.'), 500);
        }
    }

    public function markAllRead(Request $request): JsonResponse
    {
        try {
            $request->user()->unreadNotifications->markAsRead();

            return ApiResponse::success(null, __('All notifications marked as read.'));
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error(__('Couldn\'t mark notifications as read. Please try again.'), 500);
        }
    }

    public function pushSubscriptionStatus(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $count = $user->pushSubscriptions()->count();

            return ApiResponse::success([
                'subscribed' => $count > 0,
                'count' => $count,
            ]);
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error(__('Could not check browser alert status.'), 500);
        }
    }

    public function testPushNotification(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $this->staffPushSubscriptionService->assertStaffRecipient($user);

            if (! $user->pushSubscriptions()->exists()) {
                return ApiResponse::error(
                    __('No browser subscription on this device. Click Enable browser alerts and allow notifications first.'),
                    422
                );
            }

            app(StaffWebPushService::class)->sendNow($user, [
                'title' => __('Test order alert'),
                'body' => __('Browser notifications are working. Close this tab and you should still see popups for new orders.'),
                'url' => StaffNotificationUrl::sanitize(route('admin.dashboard')),
            ]);

            return ApiResponse::success(null, __('Test sent. With this tab open you will only see the in-app alert. Minimize or close the tab to confirm the Windows popup.'));
        } catch (AuthorizationException $e) {
            return ApiResponse::error($e->getMessage(), 403);
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error(__('Could not send test notification. Please try again.'), 500);
        }
    }

    public function storePushSubscription(StorePushSubscriptionRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $this->staffPushSubscriptionService->assertStaffRecipient($user);

            $validated = $request->validated();

            $user->updatePushSubscription(
                $validated['endpoint'],
                $validated['keys']['p256dh'],
                $validated['keys']['auth']
            );

            return ApiResponse::success(null, __('Browser alerts enabled. You will receive notifications even when this browser is closed, until you log out.'));
        } catch (AuthorizationException $e) {
            return ApiResponse::error($e->getMessage(), 403);
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error(
                __('Browser alerts aren\'t available. In-app notifications still work.'),
                500
            );
        }
    }

    public function destroyPushSubscription(DestroyPushSubscriptionRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            PushSubscription::query()
                ->where('subscribable_id', $request->user()->id)
                ->where('subscribable_type', $request->user()::class)
                ->where('endpoint', $validated['endpoint'])
                ->delete();

            return ApiResponse::success(null, __('Browser alerts disabled.'));
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error(__('Couldn\'t disable browser alerts.'), 500);
        }
    }

    private function findOwnedNotification(Request $request, string $id): ?DatabaseNotification
    {
        return $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatNotification(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'type' => $data['type'] ?? null,
            'title' => $data['title'] ?? '',
            'message' => $data['message'] ?? '',
            'url' => $data['url'] ?? '#',
            'highlight_target' => $data['highlight_target'] ?? null,
            'order_no' => $data['order_no'] ?? null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'created_human' => $notification->created_at?->diffForHumans(),
        ];
    }
}

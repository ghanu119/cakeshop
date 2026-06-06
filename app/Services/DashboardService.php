<?php

namespace App\Services;

class DashboardService
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * @return array{
     *     stats: array{
     *         deliveriesToday: int,
     *         inKitchen: int,
     *         awaitingVerification: int,
     *         revenueToday: float,
     *         ordersThisWeek: int
     *     },
     *     todayDeliveries: \Illuminate\Database\Eloquent\Collection,
     *     todayDeliveriesTotal: int,
     *     inKitchenOrders: \Illuminate\Database\Eloquent\Collection,
     *     inKitchenOverdueCount: int,
     *     upcomingOrders: \Illuminate\Database\Eloquent\Collection,
     *     upcomingTotal: int,
     *     paymentReviewOrders: \Illuminate\Database\Eloquent\Collection,
     *     paymentReviewTotal: int,
     *     recentOrders: \Illuminate\Database\Eloquent\Collection
     * }
     */
    public function dataForAdmin(): array
    {
        $inKitchenOrders = $this->orderService->listKitchenTodayForDashboard();
        $tz = settings('timezone') ?? 'Asia/Kolkata';
        $inKitchenOverdueCount = $inKitchenOrders->filter(function ($order) use ($tz) {
            $prep = $order->preparation_at?->setTimezone($tz);

            return $prep && $prep->isPast() && $order->order_status === 'processing';
        })->count();

        return [
            'stats' => $this->orderService->adminDashboardStats(),
            'todayDeliveries' => $this->orderService->listAdminTodayDeliveriesPreview(),
            'todayDeliveriesTotal' => $this->orderService->countAdminTodayDeliveries(),
            'inKitchenOrders' => $inKitchenOrders->take(OrderService::ADMIN_IN_KITCHEN_PREVIEW_LIMIT),
            'inKitchenTotal' => $inKitchenOrders->count(),
            'inKitchenOverdueCount' => $inKitchenOverdueCount,
            'upcomingOrders' => $this->orderService->listAdminUpcomingPreview(),
            'upcomingTotal' => $this->orderService->countAdminUpcoming(),
            'paymentReviewOrders' => $this->orderService->listAdminPaymentReviewPreview(),
            'paymentReviewTotal' => $this->orderService->countAdminPaymentReview(),
            'recentOrders' => $this->orderService->listAdminRecentOrders(),
        ];
    }
}

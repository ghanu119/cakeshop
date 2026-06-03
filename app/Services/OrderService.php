<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class OrderService
{
    public function __construct(
        private ProductVariantService $productVariantService
    ) {}

    public function listForAdmin(Request $request): LengthAwarePaginator
    {
        $query = Order::query()->with(['product', 'media']);

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('guest_phone', 'like', "%{$term}%")
                    ->orWhere('guest_name', 'like', "%{$term}%")
                    ->orWhere('uuid', 'like', "%{$term}%");
            });
        }
        if ($request->filled('order_status')) {
            $query->where('order_status', $request->input('order_status'));
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('ordered_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('ordered_at', '<=', $request->input('to_date'));
        }

        return $query->orderByDesc('ordered_at')->paginate(15)->withQueryString();
    }

    public function listForKitchen(): LengthAwarePaginator
    {
        return Order::query()
            ->with(['product'])
            ->visibleToKitchen()
            ->orderBy('delivery_at')
            ->paginate(20);
    }

    public function createOrder(Product $product, array $data): Order
    {
        $order = new Order;
        $order->guest_name = $data['guest_name'];
        $order->guest_email = $data['guest_email'] ?? null;
        $order->guest_phone = $data['guest_phone'];
        $order->product_id = $product->id;
        $order->product_name = $product->name_en;
        $order->quantity = (int) ($data['quantity'] ?? 1);
        $order->message_on_cake = $data['message_on_cake'] ?? null;
        $order->instructions = $data['instructions'] ?? null;
        $order->payment_status = 'pending';
        $order->order_status = 'pending';
        $tz = settings('timezone') ?? 'Asia/Kolkata';
        $order->delivery_at = Carbon::parse($data['delivery_at'], $tz)->utc();
        $order->ordered_at = now();

        if ($this->productVariantService->hasVariants($product)) {
            $variant = $this->productVariantService->findVariantForProduct(
                $product,
                (int) $data['product_variant_id']
            );
            $order->product_variant_id = $variant->id;
            $order->unit_price = $variant->price;
            $order->amount = $variant->price * $order->quantity;
            $order->save();
            $this->productVariantService->snapshotOrder($product, $variant, $order);
        } else {
            $order->unit_price = $product->price;
            $order->amount = $product->price * $order->quantity;
            $order->save();
        }

        return $order;
    }

    public function submitPaymentDetails(Order $order, array $data): void
    {
        $order->payment_reference = $data['payment_reference'] ?? null;
        $order->payment_amount = isset($data['payment_amount']) ? (float) $data['payment_amount'] : null;
        $order->payment_made_at = isset($data['payment_made_at']) ? Carbon::parse($data['payment_made_at'])->utc() : null;
        $order->save();
    }

    public function verifyPayment(Order $order): void
    {
        $order->payment_status = 'verified';
        $order->save();
    }

    public function updateOrderStatus(Order $order, string $orderStatus): void
    {
        $order->order_status = $orderStatus;
        $order->save();
    }

    public function updateSerialNumber(Order $order, ?string $serialNumber): void
    {
        $order->serial_number = $serialNumber ?: null;
        $order->save();
    }

    public function deliveryAtRules(): array
    {
        $timezone = settings('timezone') ?? 'Asia/Kolkata';
        $maxDays = (int) (settings('order_max_future_days') ?? 7);
        $minHours = (int) (settings('order_min_hours_before_delivery') ?? 4);

        $now = Carbon::now($timezone);
        $minDelivery = $now->copy()->addHours($minHours);
        $maxDelivery = $now->copy()->addDays($maxDays)->endOfDay();

        return [
            'after' => $minDelivery->utc(),
            'before' => $maxDelivery->utc(),
            'timezone' => $timezone,
        ];
    }
}

<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderYearSequence;
use App\Models\Product;
use App\Models\Setting;
use App\Services\OrderNumberService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('timezone', 'Asia/Kolkata');
        Setting::flushCache();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_first_order_of_year_gets_sequence_001(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-15 10:00:00', 'Asia/Kolkata'));

        $order = $this->createOrder();

        $this->assertSame('BB-20260315-001', $order->order_no);
        $this->assertSame(1, OrderYearSequence::query()->where('year', 2026)->value('last_number'));
    }

    public function test_second_order_same_year_increments_sequence(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-15 10:00:00', 'Asia/Kolkata'));
        $this->createOrder();

        Carbon::setTestNow(Carbon::parse('2026-06-06 14:00:00', 'Asia/Kolkata'));
        $second = $this->createOrder();

        $this->assertSame('BB-20260606-002', $second->order_no);
        $this->assertSame(2, OrderYearSequence::query()->where('year', 2026)->value('last_number'));
    }

    public function test_sequence_resets_on_new_calendar_year(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-31 23:00:00', 'Asia/Kolkata'));
        $this->createOrder();

        Carbon::setTestNow(Carbon::parse('2027-01-01 09:00:00', 'Asia/Kolkata'));
        $firstOfYear = $this->createOrder();

        $this->assertSame('BB-20270101-001', $firstOfYear->order_no);
        $this->assertSame(1, OrderYearSequence::query()->where('year', 2027)->value('last_number'));
    }

    public function test_assign_next_can_be_called_directly(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-01 08:00:00', 'Asia/Kolkata'));

        $order = new Order;
        $order->ordered_at = now();

        $orderNo = app(OrderNumberService::class)->assignNext($order);

        $this->assertSame('BB-20260101-001', $orderNo);
    }

    private function createOrder(): Order
    {
        $product = Product::factory()->create();

        return Order::factory()->for($product)->create([
            'ordered_at' => now(),
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class OrderProductImagePreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Storage::fake('public');
        Setting::set('timezone', 'Asia/Kolkata');
        Setting::flushCache();
    }

    public function test_admin_order_show_includes_product_image_lightbox(): void
    {
        $admin = $this->adminUser();
        $product = $this->productWithImages(2);
        $order = Order::factory()->verified()->for($product)->create();

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertSee('data-image-lightbox', false);
        $response->assertSee('data-image-lightbox-items', false);
        $response->assertSee(__('Product reference'), false);
        $response->assertSee('admin-product-ref-gallery__thumb', false);

        foreach ($product->orderedProductImages() as $media) {
            $response->assertSee($product->productImageUrl($media, 'thumb'), false);
        }
    }

    public function test_kitchen_order_show_includes_product_image_lightbox(): void
    {
        $admin = $this->adminUser();
        $kitchen = $this->kitchenUser();
        $product = $this->productWithImages(2);
        $order = $this->verifiedOrderTodayForProduct($product);
        $order->update(['message_on_cake' => 'Happy Birthday']);

        $prepAt = Carbon::now('Asia/Kolkata')->addHours(2)->format('Y-m-d\TH:i');
        $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $prepAt,
        ])->assertRedirect();

        $response = $this->actingAs($kitchen)->get(route('admin.kitchen.orders.show', $order));

        $response->assertOk();
        $response->assertSee('data-image-lightbox', false);
        $response->assertSee('data-image-lightbox-items', false);
        $response->assertSee(__('Product reference'), false);
        $response->assertSee('admin-product-ref-gallery__thumb', false);
        $response->assertSee('admin-product-ref-gallery w-full', false);

        $content = $response->getContent();
        $productRefPos = strpos($content, (string) __('Product reference'));
        $customizationPos = strpos($content, (string) __('Cake Customization'));
        $this->assertNotFalse($productRefPos);
        $this->assertNotFalse($customizationPos);
        $this->assertLessThan($customizationPos, $productRefPos);
    }

    public function test_better_buns_confirm_shows_gallery_and_product_link(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        $product = $this->productWithImages(2);
        $order = Order::factory()->for($product)->create([
            'product_name' => $product->name_en,
        ]);

        $response = $this->get(route('order.confirm', $order));

        $response->assertOk();
        $response->assertSee(__('Order Confirmed!'), false);
        $response->assertSee('js-product-gallery-lightbox', false);
        $response->assertSee('data-gallery-items', false);
        $response->assertSee($product->name_en, false);
        $response->assertSee(route('products.show', $product->slug), false);
        $response->assertSee($product->productImageUrl($product->orderedProductImages()->first(), 'medium'), false);
    }

    public function test_better_buns_confirm_omits_product_link_when_product_trashed(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        $product = $this->productWithImages(1);
        $order = Order::factory()->for($product)->create([
            'product_name' => $product->name_en,
        ]);

        $product->delete();

        $response = $this->get(route('order.confirm', $order));

        $response->assertOk();
        $response->assertSee($product->name_en, false);
        $response->assertSee(__('no longer available'), false);
        $response->assertDontSee(route('products.show', $product->slug), false);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        return $admin;
    }

    private function kitchenUser(): User
    {
        $kitchen = User::factory()->create(['email_verified_at' => now()]);
        $kitchen->assignRole('Kitchen');

        return $kitchen;
    }

    private function productWithImages(int $count): Product
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $mediaIds = [];
        for ($i = 0; $i < $count; $i++) {
            $path = UploadedFile::fake()->image("cake{$i}.jpg")->store('temp', 'public');
            $media = $product->addMedia(Storage::disk('public')->path($path))->toMediaCollection('product_images');
            $mediaIds[] = $media->id;
        }
        if (count($mediaIds) > 1) {
            Media::setNewOrder($mediaIds);
        }

        return $product->fresh();
    }

    private function verifiedOrderTodayForProduct(Product $product): Order
    {
        $tz = 'Asia/Kolkata';
        $now = Carbon::now($tz);
        $deliveryAt = $now->copy()->addHours(6);

        if (! $deliveryAt->isSameDay($now)) {
            $deliveryAt = $now->copy()->endOfDay()->subHours(2);
        }

        return Order::factory()
            ->verified()
            ->for($product)
            ->create([
                'delivery_at' => $deliveryAt->utc(),
            ]);
    }
}

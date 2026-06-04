<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductImageTempService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class ProductImagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Storage::fake('public');
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        return $admin;
    }

    public function test_guest_cannot_upload_temp_product_image(): void
    {
        $response = $this->post(route('admin.products.images.temp.store'), [
            'image' => UploadedFile::fake()->image('cake.jpg'),
        ]);

        $response->assertRedirect();
    }

    public function test_admin_can_upload_temp_product_image(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.products.images.temp.store'), [
            'image' => UploadedFile::fake()->image('cake.jpg'),
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'url', 'name']);

        $token = $response->json('token');
        $this->assertStringStartsWith('/storage/', $response->json('url'));
        $this->assertNotNull(Cache::get('product_image_temp:'.$token));
        Storage::disk('public')->assertExists('temp/product-images/'.$admin->id.'/'.$token.'.jpg');
    }

    public function test_store_product_attaches_temp_images_in_order(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();

        $tempA = app(ProductImageTempService::class)->store(
            UploadedFile::fake()->image('a.jpg'),
            $admin
        );
        $tempB = app(ProductImageTempService::class)->store(
            UploadedFile::fake()->image('b.jpg'),
            $admin
        );

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name_en' => 'Gallery Cake',
            'price' => 500,
            'status' => 'active',
            'product_images' => [
                'temp:'.$tempB['token'],
                'temp:'.$tempA['token'],
            ],
            'primary_image' => 'temp:'.$tempA['token'],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('name_en', 'Gallery Cake')->first();
        $this->assertNotNull($product);
        $this->assertCount(2, $product->getMedia('product_images'));

        $ordered = $product->orderedProductImages();
        $this->assertSame(1, $ordered->first()->order_column);
        $this->assertCount(2, $ordered);
    }

    public function test_update_product_rejects_foreign_temp_token(): void
    {
        $owner = $this->adminUser();
        $other = $this->adminUser();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $temp = app(ProductImageTempService::class)->store(
            UploadedFile::fake()->image('other.jpg'),
            $other
        );

        $response = $this->actingAs($owner)->put(route('admin.products.update', $product), [
            'category_id' => $category->id,
            'name_en' => $product->name_en,
            'price' => $product->price,
            'status' => 'active',
            'product_images' => ['temp:'.$temp['token']],
            'primary_image' => 'temp:'.$temp['token'],
        ]);

        $response->assertSessionHasErrors('product_images');
    }

    public function test_update_product_can_remove_existing_media(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $path = UploadedFile::fake()->image('keep.jpg')->store('temp', 'public');
        $keep = $product->addMedia(Storage::disk('public')->path($path))->toMediaCollection('product_images');
        $path2 = UploadedFile::fake()->image('drop.jpg')->store('temp', 'public');
        $drop = $product->addMedia(Storage::disk('public')->path($path2))->toMediaCollection('product_images');
        Media::setNewOrder([$keep->id, $drop->id]);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $category->id,
            'name_en' => $product->name_en,
            'price' => $product->price,
            'status' => 'active',
            'product_images' => ['existing:'.$keep->id],
            'primary_image' => 'existing:'.$keep->id,
            'removed_media_ids' => [$drop->id],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $product->refresh();
        $this->assertCount(1, $product->getMedia('product_images'));
        $this->assertSame($keep->id, $product->orderedProductImages()->first()->id);
    }

    public function test_product_store_rejects_more_than_max_images(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();
        $refs = [];

        for ($i = 0; $i < 11; $i++) {
            $temp = app(ProductImageTempService::class)->store(
                UploadedFile::fake()->image("img{$i}.jpg"),
                $admin
            );
            $refs[] = 'temp:'.$temp['token'];
        }

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name_en' => 'Too Many Images',
            'price' => 100,
            'status' => 'active',
            'product_images' => $refs,
        ]);

        $response->assertSessionHasErrors('product_images');
    }
}

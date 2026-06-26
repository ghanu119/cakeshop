<?php

namespace Tests\Feature;

use App\Models\Slider;
use App\Models\SliderItem;
use App\Models\User;
use App\Services\SliderItemImageTempService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SliderAdminTest extends TestCase
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

    private function homeSlider(): Slider
    {
        return Slider::query()->bySlug(Slider::SLUG_HOME)->firstOrFail();
    }

    public function test_admin_can_view_sliders_index(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('admin.sliders.index'));

        $response->assertOk();
        $response->assertSee('Home Slider', false);
    }

    public function test_admin_can_view_slider_items_index(): void
    {
        $admin = $this->adminUser();
        $slider = $this->homeSlider();
        SliderItem::factory()->for($slider)->withImage()->create(['title' => 'Summer Promo']);

        $response = $this->actingAs($admin)->get(route('admin.sliders.items.index', $slider));

        $response->assertOk();
        $response->assertSee('Summer Promo', false);
    }

    public function test_non_admin_cannot_create_slider_item(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $slider = $this->homeSlider();

        $response = $this->actingAs($user)->postJson(route('admin.sliders.items.store', $slider), [
            'type' => SliderItem::TYPE_IMAGE,
            'title' => 'Blocked Slide',
            'sort_order' => 1,
            'is_active' => 1,
            'slide_image_ref' => 'temp:00000000-0000-0000-0000-000000000001',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('slider_items', ['title' => 'Blocked Slide']);
    }

    public function test_guest_cannot_upload_temp_slider_image(): void
    {
        $response = $this->post(route('admin.sliders.items.images.temp.store'), [
            'image' => UploadedFile::fake()->image('slide.jpg'),
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('login', $response->headers->get('Location'));
    }

    public function test_admin_can_upload_temp_slider_image(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.sliders.items.images.temp.store'), [
            'image' => UploadedFile::fake()->image('slide.jpg', 1690, 790),
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.token', fn ($token) => is_string($token) && $token !== '');
    }

    public function test_admin_cannot_upload_oversized_temp_slider_image(): void
    {
        $admin = $this->adminUser();
        Cache::flush();

        $response = $this->actingAs($admin)->post(route('admin.sliders.items.images.temp.store'), [
            'image' => UploadedFile::fake()->image('huge.jpg')->size(3 * 1024 * 1024),
        ]);

        $response->assertSessionHasErrors('image');
    }

    public function test_admin_can_create_image_slider_item(): void
    {
        $admin = $this->adminUser();
        $slider = $this->homeSlider();
        $temp = app(SliderItemImageTempService::class)->store(
            UploadedFile::fake()->image('slide.jpg', 1690, 790),
            $admin
        );

        $response = $this->actingAs($admin)->post(route('admin.sliders.items.store', $slider), [
            'type' => SliderItem::TYPE_IMAGE,
            'title' => 'Hero Slide',
            'link' => '/products',
            'sort_order' => 5,
            'is_active' => 1,
            'slide_image_ref' => 'temp:'.$temp['token'],
        ]);

        $response->assertRedirect(route('admin.sliders.items.index', $slider));

        $item = SliderItem::where('title', 'Hero Slide')->first();
        $this->assertNotNull($item);
        $this->assertTrue($item->hasImage());
        $this->assertSame($slider->id, $item->slider_id);
    }

    public function test_admin_can_create_video_slider_item(): void
    {
        $admin = $this->adminUser();
        $slider = $this->homeSlider();

        $response = $this->actingAs($admin)->post(route('admin.sliders.items.store', $slider), [
            'type' => SliderItem::TYPE_VIDEO,
            'title' => 'Video Hero',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'sort_order' => 2,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.sliders.items.index', $slider));

        $this->assertDatabaseHas('slider_items', [
            'title' => 'Video Hero',
            'type' => SliderItem::TYPE_VIDEO,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }

    public function test_admin_cannot_create_image_item_without_image(): void
    {
        $admin = $this->adminUser();
        $slider = $this->homeSlider();

        $response = $this->actingAs($admin)->post(route('admin.sliders.items.store', $slider), [
            'type' => SliderItem::TYPE_IMAGE,
            'title' => 'No Image Slide',
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('slide_image_ref');
        $this->assertDatabaseMissing('slider_items', ['title' => 'No Image Slide']);
    }

    public function test_admin_can_edit_slider_item(): void
    {
        $admin = $this->adminUser();
        $slider = $this->homeSlider();
        $item = SliderItem::factory()->for($slider)->withImage()->create(['title' => 'Editable Slide']);

        $response = $this->actingAs($admin)->get(route('admin.sliders.items.edit', [$slider, $item]));

        $response->assertOk();
        $response->assertSee('Editable Slide', false);
    }

    public function test_admin_can_update_slider_item_without_replacing_image(): void
    {
        $admin = $this->adminUser();
        $slider = $this->homeSlider();
        $item = SliderItem::factory()->for($slider)->withImage()->create(['title' => 'Keep Image']);
        $mediaId = $item->slideImageMedia()->id;

        $response = $this->actingAs($admin)->put(route('admin.sliders.items.update', [$slider, $item]), [
            'type' => SliderItem::TYPE_IMAGE,
            'title' => 'Updated Title',
            'sort_order' => 3,
            'is_active' => 1,
            'slide_image_ref' => 'existing:'.$mediaId,
        ]);

        $response->assertRedirect(route('admin.sliders.items.index', $slider));
        $item->refresh();
        $this->assertSame('Updated Title', $item->title);
        $this->assertTrue($item->hasImage());
    }

    public function test_admin_can_clear_optional_slider_item_fields(): void
    {
        $admin = $this->adminUser();
        $slider = $this->homeSlider();
        $item = SliderItem::factory()->for($slider)->withImage()->create([
            'title' => 'Clear Me',
            'link' => '/products',
        ]);
        $mediaId = $item->slideImageMedia()->id;

        $response = $this->actingAs($admin)->put(route('admin.sliders.items.update', [$slider, $item]), [
            'type' => SliderItem::TYPE_IMAGE,
            'title' => '',
            'link' => '',
            'sort_order' => 1,
            'is_active' => 1,
            'slide_image_ref' => 'existing:'.$mediaId,
        ]);

        $response->assertRedirect(route('admin.sliders.items.index', $slider));
        $item->refresh();
        $this->assertNull($item->title);
        $this->assertNull($item->link);
    }

    public function test_admin_can_delete_slider_item(): void
    {
        $admin = $this->adminUser();
        $slider = $this->homeSlider();
        $item = SliderItem::factory()->for($slider)->withImage()->create();

        $response = $this->actingAs($admin)->delete(route('admin.sliders.items.destroy', [$slider, $item]));

        $response->assertRedirect(route('admin.sliders.items.index', $slider));
        $this->assertSoftDeleted('slider_items', ['id' => $item->id]);
    }

    public function test_old_home_sliders_url_redirects_to_sliders(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/admin/home-sliders');

        $response->assertRedirect('/admin/sliders');
    }

    public function test_admin_can_deactivate_slider(): void
    {
        $admin = $this->adminUser();
        $slider = $this->homeSlider();
        $this->assertTrue($slider->is_active);

        $response = $this->actingAs($admin)->patch(route('admin.sliders.update', $slider), [
            'is_active' => 0,
        ]);

        $response->assertRedirect(route('admin.sliders.index'));
        $slider->refresh();
        $this->assertFalse($slider->is_active);
    }

    public function test_admin_can_activate_slider(): void
    {
        $admin = $this->adminUser();
        $slider = $this->homeSlider();
        $slider->update(['is_active' => false]);

        $response = $this->actingAs($admin)->patch(route('admin.sliders.update', $slider), [
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.sliders.index'));
        $slider->refresh();
        $this->assertTrue($slider->is_active);
    }
}

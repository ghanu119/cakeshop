<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Slider;
use App\Models\SliderItem;
use Hexadog\ThemesManager\Facades\ThemesManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SliderStorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ThemesManager::set('cakeshop/better-buns');
    }

    private function useBetterBunsTheme(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();
    }

    private function homeSlider(): Slider
    {
        return Slider::query()->bySlug(Slider::SLUG_HOME)->firstOrFail();
    }

    public function test_better_buns_homepage_shows_active_slider_title_and_image(): void
    {
        $this->useBetterBunsTheme();
        $slider = $this->homeSlider();

        $item = SliderItem::factory()->for($slider)->withImage()->create([
            'title' => 'Better Buns Hero Title',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Better Buns Hero Title', false);
        $response->assertSee($item->imageUrl('large'), false);
        $response->assertSee('js-home-slider', false);
        $response->assertSee('home-slider-slide__overlay', false);
    }

    public function test_better_buns_homepage_renders_slider_link(): void
    {
        $this->useBetterBunsTheme();
        $slider = $this->homeSlider();

        SliderItem::factory()->for($slider)->withImage()->create([
            'title' => null,
            'link' => '/products',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('href="/products"', false);
        $response->assertDontSee('home-slider-slide__overlay', false);
    }

    public function test_better_buns_homepage_renders_youtube_video_slide(): void
    {
        $this->useBetterBunsTheme();
        $slider = $this->homeSlider();

        SliderItem::factory()->for($slider)->video()->create([
            'title' => 'Video Slide',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
        $response->assertSee('Video Slide', false);
    }

    public function test_inactive_slider_item_falls_back_to_static_hero(): void
    {
        $this->useBetterBunsTheme();
        $slider = $this->homeSlider();

        SliderItem::factory()->for($slider)->withImage()->inactive()->create([
            'title' => 'Hidden Slide Title',
            'sort_order' => 1,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Hidden Slide Title', false);
        $response->assertDontSee('js-home-slider', false);
        $response->assertSee(__('Sweetness'), false);
        $response->assertSee(__('Redefined'), false);
    }

    public function test_better_buns_homepage_falls_back_to_static_hero_without_slider_items(): void
    {
        $this->useBetterBunsTheme();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('js-home-slider', false);
        $response->assertSee(__('Sweetness'), false);
        $response->assertSee(__('Redefined'), false);
    }

    public function test_inactive_home_slider_falls_back_to_static_hero(): void
    {
        $this->useBetterBunsTheme();
        $slider = $this->homeSlider();
        $slider->update(['is_active' => false]);

        SliderItem::factory()->for($slider)->withImage()->create([
            'title' => 'Should Not Appear',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Should Not Appear', false);
        $response->assertDontSee('js-home-slider', false);
        $response->assertSee(__('Sweetness'), false);
        $response->assertSee(__('Redefined'), false);
    }
}

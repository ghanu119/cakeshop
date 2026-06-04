<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('theme', 'warm');
        Setting::flushCache();
    }

    public function test_about_page_displays_our_story_timeline(): void
    {
        $response = $this->get(route('about'));

        $response->assertOk();
        $response->assertSee(__('Our Story'), false);
        $response->assertSee(__('The beginning'), false);
        $response->assertSee('data-testid="our-story-timeline"', false);
        $response->assertSee('lg:grid-cols-4', false);
    }
}

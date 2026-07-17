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

    public function test_about_page_displays_better_buns_story_content(): void
    {
        $response = $this->get(route('about'));

        $response->assertOk();
        $response->assertSee(__('About Better Buns Live Bakery'), false);
        $response->assertSee(__('Baking Freshness Since August 2015'), false);
        $response->assertSee(__('August 2015'), false);
        $response->assertSee('data-testid="our-story-timeline"', false);
        $response->assertSee('lg:grid-cols-4', false);
    }
}

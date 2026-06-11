<?php

namespace Tests\Feature;

use App\Models\VariantOptionType;
use App\Services\VariantOptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariantOptionTypeSlugSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_deleted_type_releases_slug_on_delete(): void
    {
        $type = VariantOptionType::create([
            'slug' => 'frosting',
            'name_en' => 'Frosting',
            'selection_mode' => 'single',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $type->delete();

        $type->refresh();
        $this->assertSoftDeleted($type);
        $this->assertSame('frosting-deleted-'.$type->id, $type->slug);
    }

    public function test_service_can_recreate_type_with_same_slug_after_soft_delete(): void
    {
        $deleted = VariantOptionType::create([
            'slug' => 'frosting',
            'name_en' => 'Frosting',
            'selection_mode' => 'single',
            'sort_order' => 1,
            'status' => 'active',
        ]);
        $deleted->delete();

        $type = app(VariantOptionService::class)->createOrUpdateType(null, [
            'slug' => 'frosting',
            'name_en' => 'Frosting',
            'selection_mode' => 'single',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $this->assertSame('frosting', $type->slug);
        $this->assertDatabaseHas('variant_option_types', [
            'slug' => 'frosting',
            'deleted_at' => null,
        ]);
    }

    public function test_service_reclaims_slug_from_existing_soft_deleted_type(): void
    {
        $deleted = VariantOptionType::create([
            'slug' => 'frosting',
            'name_en' => 'Frosting',
            'selection_mode' => 'single',
            'sort_order' => 1,
            'status' => 'active',
        ]);
        $deleted->delete();
        $deleted->update(['slug' => 'frosting']);

        $type = app(VariantOptionService::class)->createOrUpdateType(null, [
            'slug' => 'frosting',
            'name_en' => 'Frosting Type',
            'selection_mode' => 'single',
            'sort_order' => 2,
            'status' => 'active',
        ]);

        $this->assertSame('frosting', $type->slug);
        $deleted->refresh();
        $this->assertSame('frosting-deleted-'.$deleted->id, $deleted->slug);
    }
}

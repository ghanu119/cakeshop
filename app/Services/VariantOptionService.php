<?php

namespace App\Services;

use App\Models\VariantOptionType;
use App\Models\VariantOptionValue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class VariantOptionService
{
    public function listTypes(Request $request): LengthAwarePaginator
    {
        return VariantOptionType::query()
            ->withCount('values')
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->paginate(15)
            ->withQueryString();
    }

    public function listValues(VariantOptionType $type, Request $request): LengthAwarePaginator
    {
        return $type->values()
            ->orderBy('sort_order')
            ->orderBy('grams')
            ->paginate(20)
            ->withQueryString();
    }

    public function createOrUpdateType(?VariantOptionType $type, array $data): VariantOptionType
    {
        $type = $type ?? new VariantOptionType;

        $conflict = VariantOptionType::withTrashed()
            ->where('slug', $data['slug'])
            ->when($type->exists, fn ($query) => $query->where('id', '!=', $type->id))
            ->first();

        if ($conflict?->trashed()) {
            $conflict->releaseSlugForSoftDelete();
        }

        $type->slug = $data['slug'];
        $type->name_en = $data['name_en'];
        $type->selection_mode = $data['selection_mode'] ?? 'single';
        $type->sort_order = (int) ($data['sort_order'] ?? 0);
        $type->status = $data['status'] ?? 'active';
        $type->save();

        return $type;
    }

    public function createOrUpdateValue(?VariantOptionValue $value, VariantOptionType $type, array $data): VariantOptionValue
    {
        $value = $value ?? new VariantOptionValue;
        $value->variant_option_type_id = $type->id;
        $value->label = $data['label'];
        $value->person_capacity_label = $data['person_capacity_label'] ?? null;
        $value->grams = isset($data['grams']) && $data['grams'] !== '' ? (int) $data['grams'] : null;
        $value->delivery_charge = isset($data['delivery_charge']) && $data['delivery_charge'] !== ''
            ? (float) $data['delivery_charge']
            : null;
        $value->sort_order = (int) ($data['sort_order'] ?? 0);
        $value->status = $data['status'] ?? 'active';
        $value->save();

        return $value;
    }

    public function activeWeightValues()
    {
        return VariantOptionValue::query()
            ->forTypeSlug('weight')
            ->whereHas('type', fn ($q) => $q->active())
            ->active()
            ->orderBy('sort_order')
            ->orderBy('grams')
            ->get();
    }

    public function weightType(): ?VariantOptionType
    {
        return VariantOptionType::query()->slug('weight')->first();
    }

    /**
     * Ensure the weight option type exists (seeded in production; created on first visit in dev).
     */
    public function ensureWeightType(): VariantOptionType
    {
        $type = VariantOptionType::query()->slug('weight')->first();

        if ($type) {
            return $type;
        }

        return $this->createOrUpdateType(null, [
            'slug' => 'weight',
            'name_en' => 'Weight',
            'selection_mode' => 'single',
            'sort_order' => 1,
            'status' => 'active',
        ]);
    }

    public function deleteValueIfUnused(VariantOptionValue $value): bool
    {
        if ($value->productVariantSelections()->exists()) {
            return false;
        }

        $value->delete();

        return true;
    }
}

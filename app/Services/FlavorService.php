<?php

namespace App\Services;

use App\Models\Flavor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FlavorService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $query = Flavor::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query->orderBy('sort_order')->orderBy('name_en')->paginate(15)->withQueryString();
    }

    public function createOrUpdate(?Flavor $flavor, array $data): Flavor
    {
        $flavor = $flavor ?? new Flavor;

        $flavor->name_en = $data['name_en'];
        $flavor->status = $data['status'] ?? 'active';
        $flavor->sort_order = (int) ($data['sort_order'] ?? 0);
        $flavor->badge_color = $data['badge_color'] ?? null;

        $flavor->slug = $this->resolveUniqueSlug($flavor, $data['name_en']);

        $flavor->save();

        return $flavor;
    }

    private function resolveUniqueSlug(Flavor $flavor, string $nameEn): string
    {
        $slugBase = Str::slug($nameEn);
        $slug = $slugBase;
        $count = 0;

        while (true) {
            $conflict = Flavor::withTrashed()
                ->where('slug', $slug)
                ->when($flavor->exists, fn ($query) => $query->where('id', '!=', $flavor->id))
                ->first();

            if ($conflict === null) {
                return $slug;
            }

            if ($conflict->trashed()) {
                $conflict->releaseSlugForSoftDelete();

                continue;
            }

            $slug = $slugBase.'-'.(++$count);
        }
    }
}

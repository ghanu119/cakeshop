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
        $flavor->name_hi = $data['name_hi'] ?? null;
        $flavor->name_gu = $data['name_gu'] ?? null;
        $flavor->status = $data['status'] ?? 'active';
        $flavor->sort_order = (int) ($data['sort_order'] ?? 0);
        $flavor->badge_color = $data['badge_color'] ?? null;

        $slugBase = Str::slug($data['name_en']);
        $slug = $slugBase;
        $count = 0;
        while (Flavor::where('slug', $slug)->where('id', '!=', $flavor->id)->exists()) {
            $slug = $slugBase.'-'.(++$count);
        }
        $flavor->slug = $slug;

        $flavor->save();

        return $flavor;
    }
}

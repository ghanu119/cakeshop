<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where('name_en', 'like', "%{$term}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query->orderBy('sort_order')->orderBy('name_en')->paginate(15)->withQueryString();
    }

    public function createOrUpdate(?Category $category, array $data): Category
    {
        $category = $category ?? new Category;

        $category->name_en = $data['name_en'];
        $category->status = $data['status'] ?? 'active';
        $category->sort_order = (int) ($data['sort_order'] ?? 0);

        $category->slug = $this->resolveUniqueSlug($category, $data['name_en']);

        $category->save();

        return $category;
    }

    private function resolveUniqueSlug(Category $category, string $nameEn): string
    {
        $slugBase = Str::slug($nameEn);
        $slug = $slugBase;
        $count = 0;

        while (true) {
            $conflict = Category::withTrashed()
                ->where('slug', $slug)
                ->when($category->exists, fn ($query) => $query->where('id', '!=', $category->id))
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

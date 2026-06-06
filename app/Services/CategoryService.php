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

        $slugBase = Str::slug($data['name_en']);
        $slug = $slugBase;
        $count = 0;
        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = $slugBase . '-' . (++$count);
        }
        $category->slug = $slug;

        $category->save();

        return $category;
    }
}

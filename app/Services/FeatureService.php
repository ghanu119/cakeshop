<?php

namespace App\Services;

use App\Models\Feature;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FeatureService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $query = Feature::query();

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return $query->orderBy('sort_order')->orderBy('title')->paginate(15)->withQueryString();
    }

    public function createOrUpdate(?Feature $feature, array $data): Feature
    {
        $feature = $feature ?? new Feature;

        $feature->title = $data['title'];
        $feature->description = $data['description'];
        $feature->sort_order = (int) ($data['sort_order'] ?? 0);
        $feature->is_active = ! empty($data['is_active']);

        if (isset($data['icon_file']) && $data['icon_file'] instanceof UploadedFile) {
            $path = $data['icon_file']->store('features', 'public');
            $feature->icon = $path;
        } else {
            $feature->icon = isset($data['icon']) ? ($data['icon'] ?: null) : $feature->icon;
        }

        $feature->save();

        return $feature;
    }
}

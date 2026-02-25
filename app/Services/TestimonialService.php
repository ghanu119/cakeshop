<?php

namespace App\Services;

use App\Models\Testimonial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class TestimonialService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $query = Testimonial::query();

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('customer_name', 'like', "%{$term}%")
                    ->orWhere('review', 'like', "%{$term}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return $query->orderBy('sort_order')->orderBy('customer_name')->paginate(15)->withQueryString();
    }

    public function createOrUpdate(?Testimonial $testimonial, array $data): Testimonial
    {
        $testimonial = $testimonial ?? new Testimonial;

        $testimonial->customer_name = $data['customer_name'];
        $testimonial->customer_initials = $data['customer_initials'] ?? null;
        $testimonial->review = $data['review'];
        $testimonial->rating = (int) $data['rating'];
        $testimonial->is_verified = ! empty($data['is_verified']);
        $testimonial->sort_order = (int) ($data['sort_order'] ?? 0);
        $testimonial->is_active = ! empty($data['is_active']);

        $testimonial->save();

        return $testimonial;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTestimonialRequest;
use App\Http\Requests\Admin\UpdateTestimonialRequest;
use App\Models\Testimonial;
use App\Services\TestimonialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function __construct(
        private TestimonialService $testimonialService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Testimonial::class);
        $testimonials = $this->testimonialService->list(request());

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create(): View
    {
        $this->authorize('create', Testimonial::class);
        $testimonial = null;

        return view('admin.testimonials.create', compact('testimonial'));
    }

    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $this->testimonialService->createOrUpdate(null, $request->validated());

        return redirect()->route('admin.testimonials.index')->with('status', __('Testimonial created.'));
    }

    public function edit(Testimonial $testimonial): View
    {
        $this->authorize('update', $testimonial);

        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $this->testimonialService->createOrUpdate($testimonial, $request->validated());

        return redirect()->route('admin.testimonials.index')->with('status', __('Testimonial updated.'));
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('delete', $testimonial);
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('status', __('Testimonial deleted.'));
    }
}

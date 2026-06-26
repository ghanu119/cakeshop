<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSliderRequest;
use App\Models\Slider;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SliderController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Slider::class);

        $sliders = Slider::query()
            ->withCount('items')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.sliders.index', compact('sliders'));
    }

    public function update(UpdateSliderRequest $request, Slider $slider): RedirectResponse
    {
        $slider->is_active = $request->boolean('is_active');
        $slider->save();

        $message = $slider->is_active
            ? __(':name is now active on the storefront.', ['name' => $slider->name])
            : __(':name is now hidden on the storefront.', ['name' => $slider->name]);

        return redirect()->route('admin.sliders.index')->with('status', $message);
    }
}

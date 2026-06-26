<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSliderItemRequest;
use App\Http\Requests\Admin\UpdateSliderItemRequest;
use App\Models\Slider;
use App\Models\SliderItem;
use App\Services\SliderItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SliderItemController extends Controller
{
    public function __construct(
        private SliderItemService $sliderItemService
    ) {}

    public function index(Slider $slider): View
    {
        $this->authorize('viewAny', SliderItem::class);
        $sliderItems = $this->sliderItemService->listForSlider($slider, request());

        return view('admin.sliders.items.index', compact('slider', 'sliderItems'));
    }

    public function create(Slider $slider): View
    {
        $this->authorize('create', SliderItem::class);
        $sliderItem = null;

        return view('admin.sliders.items.create', compact('slider', 'sliderItem'));
    }

    public function store(StoreSliderItemRequest $request, Slider $slider): RedirectResponse
    {
        $this->sliderItemService->createOrUpdate($slider, null, $request->validated(), $request->user());

        return redirect()->route('admin.sliders.items.index', $slider)->with('status', __('Slide item created.'));
    }

    public function edit(Slider $slider, SliderItem $item): View
    {
        $this->authorize('update', $item);
        abort_unless($item->slider_id === $slider->id, 404);
        $item->load('media');
        $sliderItem = $item;

        return view('admin.sliders.items.edit', compact('slider', 'sliderItem'));
    }

    public function update(UpdateSliderItemRequest $request, Slider $slider, SliderItem $item): RedirectResponse
    {
        abort_unless($item->slider_id === $slider->id, 404);
        $this->sliderItemService->createOrUpdate($slider, $item, $request->validated(), $request->user());

        return redirect()->route('admin.sliders.items.index', $slider)->with('status', __('Slide item updated.'));
    }

    public function destroy(Slider $slider, SliderItem $item): RedirectResponse
    {
        $this->authorize('delete', $item);
        abort_unless($item->slider_id === $slider->id, 404);
        $item->delete();

        return redirect()->route('admin.sliders.items.index', $slider)->with('status', __('Slide item deleted.'));
    }
}

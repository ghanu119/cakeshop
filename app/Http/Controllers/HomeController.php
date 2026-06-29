<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Feature;
use App\Models\Slider;
use App\Models\SliderItem;
use App\Services\VideoEmbedService;
use App\Models\Product;
use App\Models\Testimonial;
use App\Services\CouponService;
use App\Services\ProductService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private CouponService $couponService,
    ) {}

    public function index(): View
    {
        $with = [
            'media',
            'variants' => fn ($q) => $q->active()->orderBy('sort_order'),
            'variants.selections.value',
            'flavors' => fn ($q) => $q->active()->orderByPivot('sort_order'),
        ];
        $highlights = Product::highlight()->with($with)->limit(8)->get();
        $trending = Product::trending()->with($with)->limit(8)->get();
        $featured = Product::featured()->with($with)->limit(8)->get();
        $products = $this->productService->listForHomepage(request());
        $categories = Category::active()->orderBy('sort_order')->get();
        $features = Feature::active()->orderBy('sort_order')->get();
        $testimonials = Testimonial::active()->orderBy('sort_order')->limit(3)->get();
        $videoEmbed = app(VideoEmbedService::class);
        $homeSlider = Slider::query()->active()->bySlug(Slider::SLUG_HOME)->first();
        $sliderItems = $homeSlider
            ? $homeSlider->items()
                ->active()
                ->with('media')
                ->orderBy('sort_order')
                ->get()
                ->filter(fn (SliderItem $item) => $item->hasContent())
                ->map(function (SliderItem $item) use ($videoEmbed) {
                    if ($item->isVideo()) {
                        $item->setAttribute('video_embed', $videoEmbed->resolve($item->video_url));
                    }

                    return $item;
                })
                ->filter(fn (SliderItem $item) => $item->isImage() || $item->getAttribute('video_embed'))
                ->values()
            : collect();

        $customer = auth('customer')->user();
        $this->couponService->attachStorefrontPromoToProducts($highlights, $customer);
        $this->couponService->attachStorefrontPromoToProducts($trending, $customer);
        $this->couponService->attachStorefrontPromoToProducts($featured, $customer);
        $this->couponService->attachStorefrontPromoToProducts($products->getCollection(), $customer);

        return view('home', compact('highlights', 'trending', 'featured', 'products', 'categories', 'features', 'testimonials', 'sliderItems'));
    }
}

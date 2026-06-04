<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Flavor;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductSeeder extends Seeder
{
    /** Cake/dessert placeholder images (800x800) for the online cake shop. */
    private const CAKE_IMAGE_BASE = 'https://loremflickr.com/800/800/cake,dessert';

    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');
        if ($categories->isEmpty()) {
            return;
        }

        $defaultCategory = $categories->get('birthday-cakes') ?? $categories->first();

        // Optional image_count: number of Spatie product_images to seed (primary = first).
        $items = [
            [
                'name_en' => 'Chocolate Truffle Cake',
                'image_count' => 4,
                'slug' => 'chocolate-truffle-cake',
                'short_description' => 'Rich dark chocolate layers with smooth truffle filling. A classic favourite.',
                'description_en' => 'Our signature Chocolate Truffle Cake is made with premium cocoa and fresh cream. Perfect for birthdays and celebrations.',
                'price' => 899,
                'category' => 'birthday-cakes',
                'is_highlight' => true,
                'is_trending' => true,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 1,
                'flavors' => ['chocolate'],
            ],
            [
                'name_en' => 'Vanilla Buttercream Cake',
                'image_count' => 3,
                'slug' => 'vanilla-buttercream-cake',
                'short_description' => 'Light sponge with silky vanilla buttercream. Elegant and delicious.',
                'description_en' => 'A timeless vanilla sponge layered with our house-made buttercream. Ideal for any occasion.',
                'price' => 699,
                'category' => 'birthday-cakes',
                'is_highlight' => true,
                'is_trending' => false,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 2,
                'flavors' => ['vanilla'],
            ],
            [
                'name_en' => 'Red Velvet Cake',
                'image_count' => 3,
                'slug' => 'red-velvet-cake',
                'short_description' => 'Classic red velvet with cream cheese frosting. Moist and indulgent.',
                'description_en' => 'Our Red Velvet Cake features a tender crumb and tangy cream cheese frosting. A crowd-pleaser at every party.',
                'price' => 999,
                'category' => 'custom-cakes',
                'is_highlight' => true,
                'is_trending' => true,
                'is_featured' => false,
                'show_on_homepage' => true,
                'homepage_sort_order' => 3,
                'flavors' => ['red-velvet'],
            ],
            [
                'name_en' => 'Strawberry Fresh Cream Cake',
                'image_count' => 4,
                'slug' => 'strawberry-fresh-cream-cake',
                'short_description' => 'Fresh strawberries and whipped cream on a light sponge.',
                'description_en' => 'Seasonal strawberries and fresh cream on a delicate sponge. Refreshing and beautiful.',
                'price' => 849,
                'category' => 'birthday-cakes',
                'is_highlight' => false,
                'is_trending' => true,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 4,
                'flavors' => ['strawberry', 'vanilla'],
            ],
            [
                'name_en' => 'Black Forest Cake',
                'image_count' => 4,
                'slug' => 'black-forest-cake',
                'short_description' => 'Chocolate sponge, cherries, and whipped cream. A German classic.',
                'description_en' => 'Layers of chocolate cake, cherry compote, and fresh cream, finished with chocolate shavings.',
                'price' => 949,
                'category' => 'birthday-cakes',
                'is_highlight' => true,
                'is_trending' => true,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 5,
                'flavors' => ['black-forest', 'chocolate'],
            ],
            [
                'name_en' => 'Three-Tier Wedding Cake',
                'image_count' => 3,
                'slug' => 'three-tier-wedding-cake',
                'short_description' => 'Elegant vanilla and raspberry tiers. Custom design available.',
                'description_en' => 'A stunning three-tier wedding cake with vanilla and raspberry layers. We work with you on design and flavours.',
                'price' => 4999,
                'category' => 'wedding-cakes',
                'is_highlight' => false,
                'is_trending' => false,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 6,
                'flavors' => ['vanilla', 'strawberry', 'red-velvet', 'chocolate', 'butterscotch'],
            ],
            [
                'name_en' => 'Mango Mousse Cake',
                'image_count' => 2,
                'slug' => 'mango-mousse-cake',
                'short_description' => 'Silky mango mousse on a light biscuit base. Summer favourite.',
                'description_en' => 'Made with ripe Alphonso mangoes and light mousse. Perfect for summer celebrations.',
                'price' => 799,
                'category' => 'pastries-desserts',
                'is_highlight' => false,
                'is_trending' => true,
                'is_featured' => false,
                'show_on_homepage' => true,
                'homepage_sort_order' => 7,
                'flavors' => ['mango'],
            ],
            [
                'name_en' => 'Oreo Cheesecake',
                'image_count' => 3,
                'slug' => 'oreo-cheesecake',
                'short_description' => 'Creamy cheesecake with Oreo pieces and cookie base.',
                'description_en' => 'Rich cream cheese and crushed Oreos in every bite. No-bake, smooth and decadent.',
                'price' => 749,
                'category' => 'pastries-desserts',
                'is_highlight' => false,
                'is_trending' => true,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 8,
                'flavors' => ['chocolate', 'vanilla'],
            ],
            [
                'name_en' => 'Custom Photo Cake',
                'image_count' => 3,
                'slug' => 'custom-photo-cake',
                'short_description' => 'Your favourite photo printed on a sheet cake. Edible image.',
                'description_en' => 'Send us your image and we print it on a premium cake. Great for birthdays and anniversaries.',
                'price' => 1299,
                'category' => 'custom-cakes',
                'is_highlight' => false,
                'is_trending' => false,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 9,
                'flavors' => ['chocolate', 'vanilla', 'strawberry', 'red-velvet', 'butterscotch', 'black-forest'],
            ],
            [
                'name_en' => 'Assorted Cupcakes (6 pcs)',
                'image_count' => 2,
                'slug' => 'assorted-cupcakes-6',
                'short_description' => 'Six hand-decorated cupcakes in mixed flavours.',
                'description_en' => 'Chocolate, vanilla, and red velvet cupcakes with buttercream. Ideal as gifts or party favours.',
                'price' => 449,
                'category' => 'cupcakes',
                'is_highlight' => false,
                'is_trending' => true,
                'is_featured' => false,
                'show_on_homepage' => true,
                'homepage_sort_order' => 10,
                'flavors' => ['chocolate', 'vanilla', 'red-velvet'],
            ],
            [
                'name_en' => 'Pineapple Pastry (Box of 4)',
                'image_count' => 2,
                'slug' => 'pineapple-pastry-box',
                'short_description' => 'Classic pineapple pastries. Soft and fruity.',
                'description_en' => 'Four pieces of our beloved pineapple pastry. Light, fluffy, and full of flavour.',
                'price' => 199,
                'category' => 'pastries-desserts',
                'is_highlight' => false,
                'is_trending' => false,
                'is_featured' => false,
                'show_on_homepage' => true,
                'homepage_sort_order' => 11,
                'flavors' => ['pineapple'],
            ],
            [
                'name_en' => 'Festive Dry Fruit Cake',
                'slug' => 'festive-dry-fruit-cake',
                'short_description' => 'Rich fruit cake with nuts and spices. Seasonal special.',
                'description_en' => 'A dense, spiced cake loaded with dry fruits and nuts. Perfect for Diwali and Christmas.',
                'price' => 1099,
                'category' => 'seasonal-specials',
                'is_highlight' => false,
                'is_trending' => true,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 12,
                'image_count' => 3,
            ],
        ];

        foreach ($items as $item) {
            $categorySlug = $item['category'];
            $category = $categories->get($categorySlug) ?? $defaultCategory;

            $product = Product::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'category_id' => $category->id,
                    'name_en' => $item['name_en'],
                    'name_hi' => null,
                    'name_gu' => null,
                    'short_description' => $item['short_description'],
                    'description_en' => $item['description_en'] ?? $item['short_description'],
                    'description_hi' => null,
                    'description_gu' => null,
                    'price' => $item['price'],
                    'status' => 'active',
                    'meta_title' => $item['name_en'],
                    'meta_description' => $item['short_description'],
                    'show_on_homepage' => $item['show_on_homepage'] ?? false,
                    'is_highlight' => $item['is_highlight'] ?? false,
                    'is_trending' => $item['is_trending'] ?? false,
                    'is_featured' => $item['is_featured'] ?? false,
                    'homepage_sort_order' => $item['homepage_sort_order'] ?? 0,
                ]
            );

            $imageCount = (int) ($item['image_count'] ?? 1);
            if (($item['is_highlight'] ?? false) || ($item['is_trending'] ?? false) || ($item['is_featured'] ?? false)) {
                $imageCount = max($imageCount, 3);
            }
            $this->seedProductImages($product, $item['slug'], $imageCount);

            $this->syncProductFlavors($product, $item['flavors'] ?? []);
        }
    }

    private function syncProductFlavors(Product $product, array $flavorSlugs): void
    {
        if ($flavorSlugs === [] || Flavor::count() === 0) {
            return;
        }

        $flavorIds = Flavor::whereIn('slug', $flavorSlugs)->pluck('id', 'slug');
        $sync = collect($flavorSlugs)
            ->filter(fn (string $slug) => $flavorIds->has($slug))
            ->values()
            ->mapWithKeys(fn (string $slug, int $index) => [$flavorIds[$slug] => ['sort_order' => $index]])
            ->all();

        $product->flavors()->sync($sync);
    }

    private function seedProductImages(Product $product, string $slug, int $count): void
    {
        $count = max(1, min($count, 10));
        $existing = $product->getMedia('product_images')->count();

        if ($existing >= $count) {
            return;
        }

        $startIndex = $existing;
        $newMediaIds = $product->getMedia('product_images')->sortBy('order_column')->pluck('id')->all();

        for ($index = $startIndex; $index < $count; $index++) {
            $lock = abs(crc32($slug)) + $index;
            $url = self::CAKE_IMAGE_BASE.'?lock='.$lock;

            try {
                $media = $product->addMediaFromUrl($url)
                    ->toMediaCollection('product_images');
                $newMediaIds[] = $media->id;
            } catch (\Throwable $e) {
                $this->command->warn("Could not add cake image for product [{$product->slug}] (#{$index}): {$e->getMessage()}");
            }
        }

        if ($newMediaIds !== []) {
            Media::setNewOrder($newMediaIds);
        }
    }
}

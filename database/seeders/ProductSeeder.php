<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Flavor;
use App\Models\Product;
use App\Models\VariantOptionValue;
use App\Services\ProductVariantService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductSeeder extends Seeder
{
    /** Cake-only placeholder images (800x800) via Flickr tags (tag_mode=all on loremflickr). */
    private const CAKE_IMAGE_BASE = 'https://loremflickr.com/800/800';

    /** Flickr tag must match one of these (substring match) or "cake" is prepended. */
    private const CAKE_FAMILY_TAGS = ['cake', 'cupcake', 'cheesecake', 'fruitcake', 'weddingcake'];

    /** Tags that often return non-cake photos when combined with Flickr search. */
    private const BLOCKED_IMAGE_TAGS = ['photo', 'dessert', 'pastry', 'assorted', 'white', 'tier', 'oreo'];

    /** Fallback locks for generic "cake" images when a tagged fetch fails. */
    private const CAKE_FALLBACK_LOCKS = [1, 7, 13, 21, 42, 55, 88, 101, 212, 309];

    /** @var Collection<int, VariantOptionValue> */
    private Collection $weightValuesByGrams;

    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');
        if ($categories->isEmpty()) {
            return;
        }

        $this->weightValuesByGrams = VariantOptionValue::query()
            ->active()
            ->forTypeSlug('weight')
            ->get()
            ->keyBy('grams');

        $defaultCategory = $categories->get('birthday-cakes') ?? $categories->first();

        // weights: grams => INR price (synced as product variants; product.price = minimum weight price)
        // image_count: Spatie product_images (primary = first); image_tags: optional flavour hints (always normalized to cake-only)
        $items = [
            [
                'name_en' => 'Chocolate Truffle Cake',
                'name_hi' => 'चॉकलेट ट्रफल केक',
                'name_gu' => 'ચોકલેટ ટ્રફલ કેક',
                'slug' => 'chocolate-truffle-cake',
                'short_description' => 'Rich dark chocolate layers with smooth truffle filling. A classic favourite.',
                'description_en' => 'Our signature Chocolate Truffle Cake is made with premium cocoa and fresh cream. Perfect for birthdays and celebrations. Choose your size and we bake it fresh to order.',
                'ingredients' => 'Dark chocolate, fresh cream, flour, sugar, eggs, butter, cocoa powder.',
                'message_on_cake_max_length' => 40,
                'category' => 'birthday-cakes',
                'image_count' => 4,
                'image_tags' => 'cake,chocolate',
                'weights' => [500 => 899, 1000 => 1599, 2000 => 2799],
                'is_highlight' => true,
                'is_trending' => true,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 1,
                'flavors' => ['chocolate'],
            ],
            [
                'name_en' => 'Vanilla Buttercream Cake',
                'name_hi' => 'वैनिला बटरक्रीम केक',
                'name_gu' => 'વેનિલા બટરક્રીમ કેક',
                'slug' => 'vanilla-buttercream-cake',
                'short_description' => 'Light sponge with silky vanilla buttercream. Elegant and delicious.',
                'description_en' => 'A timeless vanilla sponge layered with our house-made buttercream. Ideal for any occasion — from kids’ parties to office celebrations.',
                'ingredients' => 'Vanilla extract, butter, icing sugar, flour, eggs, milk, baking powder.',
                'message_on_cake_max_length' => 40,
                'category' => 'birthday-cakes',
                'image_count' => 3,
                'image_tags' => 'cake,vanilla',
                'weights' => [500 => 699, 1000 => 1249, 2000 => 2199],
                'is_highlight' => true,
                'is_trending' => false,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 2,
                'flavors' => ['vanilla'],
            ],
            [
                'name_en' => 'Red Velvet Cake',
                'name_hi' => 'रेड वेल्वेट केक',
                'name_gu' => 'રેડ વેલ્વેટ કેક',
                'slug' => 'red-velvet-cake',
                'short_description' => 'Classic red velvet with cream cheese frosting. Moist and indulgent.',
                'description_en' => 'Our Red Velvet Cake features a tender crumb and tangy cream cheese frosting. A crowd-pleaser at every party — available in multiple sizes.',
                'ingredients' => 'Cream cheese, buttermilk, cocoa, red food colour, flour, sugar, eggs, vanilla.',
                'message_on_cake_max_length' => 40,
                'category' => 'custom-cakes',
                'image_count' => 4,
                'image_tags' => 'cake,redvelvet',
                'weights' => [500 => 999, 1000 => 1799, 2000 => 3199],
                'is_highlight' => true,
                'is_trending' => true,
                'is_featured' => false,
                'show_on_homepage' => true,
                'homepage_sort_order' => 3,
                'flavors' => ['red-velvet'],
            ],
            [
                'name_en' => 'Strawberry Fresh Cream Cake',
                'name_hi' => 'स्ट्रॉबेरी फ्रेश क्रीम केक',
                'slug' => 'strawberry-fresh-cream-cake',
                'short_description' => 'Fresh strawberries and whipped cream on a light sponge.',
                'description_en' => 'Seasonal strawberries and fresh cream on a delicate sponge. Refreshing and beautiful — best enjoyed within 24 hours of delivery.',
                'ingredients' => 'Fresh strawberries, whipped cream, sponge cake, sugar, gelatin (optional).',
                'message_on_cake_max_length' => 35,
                'category' => 'birthday-cakes',
                'image_count' => 4,
                'image_tags' => 'cake,strawberry',
                'weights' => [500 => 849, 1000 => 1499, 2000 => 2599],
                'is_highlight' => false,
                'is_trending' => true,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 4,
                'flavors' => ['strawberry', 'vanilla'],
            ],
            [
                'name_en' => 'Black Forest Cake',
                'name_hi' => 'ब्लैक फॉरेस्ट केक',
                'slug' => 'black-forest-cake',
                'short_description' => 'Chocolate sponge, cherries, and whipped cream. A German classic.',
                'description_en' => 'Layers of chocolate cake, cherry compote, and fresh cream, finished with chocolate shavings and whole cherries on top.',
                'ingredients' => 'Chocolate sponge, cherries, whipped cream, kirsch syrup, dark chocolate shavings.',
                'message_on_cake_max_length' => 40,
                'category' => 'birthday-cakes',
                'image_count' => 4,
                'image_tags' => 'cake,blackforest',
                'weights' => [500 => 949, 1000 => 1699, 2000 => 2999],
                'is_highlight' => true,
                'is_trending' => true,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 5,
                'flavors' => ['black-forest', 'chocolate'],
            ],
            [
                'name_en' => 'Three-Tier Wedding Cake',
                'name_hi' => 'थ्री-टियर वेडिंग केक',
                'slug' => 'three-tier-wedding-cake',
                'short_description' => 'Elegant vanilla and raspberry tiers. Custom design available.',
                'description_en' => 'A stunning three-tier wedding cake with vanilla and raspberry layers. We work with you on design, flavours, and delivery timing. Minimum 2 kg; larger tiers quoted on request.',
                'ingredients' => 'Vanilla sponge, raspberry compote, fondant, buttercream, edible flowers (seasonal).',
                'message_on_cake_max_length' => null,
                'category' => 'wedding-cakes',
                'image_count' => 5,
                'image_tags' => 'cake,wedding',
                'weights' => [2000 => 4999, 3000 => 7499],
                'is_highlight' => false,
                'is_trending' => false,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 6,
                'flavors' => ['vanilla', 'strawberry', 'red-velvet', 'chocolate', 'butterscotch'],
            ],
            [
                'name_en' => 'Mango Mousse Cake',
                'name_hi' => 'आम मूस केक',
                'slug' => 'mango-mousse-cake',
                'short_description' => 'Silky mango mousse on a light biscuit base. Summer favourite.',
                'description_en' => 'Made with ripe Alphonso mangoes and light mousse. Perfect for summer celebrations — no oven-heavy sponge, so it stays cool and airy.',
                'ingredients' => 'Alphonso mango pulp, cream, gelatin, biscuit base, sugar.',
                'message_on_cake_max_length' => 30,
                'category' => 'pastries-desserts',
                'image_count' => 3,
                'image_tags' => 'cake,mango',
                'weights' => [500 => 799, 1000 => 1399],
                'is_highlight' => false,
                'is_trending' => true,
                'is_featured' => false,
                'show_on_homepage' => true,
                'homepage_sort_order' => 7,
                'flavors' => ['mango'],
            ],
            [
                'name_en' => 'Oreo Cheesecake',
                'slug' => 'oreo-cheesecake',
                'short_description' => 'Creamy cheesecake with Oreo pieces and cookie base.',
                'description_en' => 'Rich cream cheese and crushed Oreos in every bite. No-bake, smooth and decadent — a hit with teens and chocolate lovers.',
                'ingredients' => 'Cream cheese, Oreo cookies, butter, sugar, whipped cream.',
                'message_on_cake_max_length' => null,
                'category' => 'pastries-desserts',
                'image_count' => 3,
                'image_tags' => 'cheesecake,cake',
                'weights' => [500 => 749, 1000 => 1299],
                'is_highlight' => false,
                'is_trending' => true,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 8,
                'flavors' => ['chocolate', 'vanilla'],
            ],
            [
                'name_en' => 'Custom Photo Cake',
                'name_hi' => 'कस्टम फोटो केक',
                'slug' => 'custom-photo-cake',
                'short_description' => 'Your favourite photo printed on a sheet cake. Edible image.',
                'description_en' => 'Send us your image and we print it on a premium cake. Great for birthdays and anniversaries. Upload a high-resolution photo at checkout; we confirm before baking.',
                'ingredients' => 'Sponge of your chosen flavour, buttercream, edible ink sheet, sugar paste border.',
                'message_on_cake_max_length' => 25,
                'category' => 'custom-cakes',
                'image_count' => 4,
                'image_tags' => 'cake,birthday',
                'weights' => [1000 => 1299, 2000 => 2299],
                'is_highlight' => false,
                'is_trending' => false,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 9,
                'flavors' => ['chocolate', 'vanilla', 'strawberry', 'red-velvet', 'butterscotch', 'black-forest'],
            ],
            [
                'name_en' => 'Assorted Cupcakes (6 pcs)',
                'slug' => 'assorted-cupcakes-6',
                'short_description' => 'Six hand-decorated cupcakes in mixed flavours.',
                'description_en' => 'Chocolate, vanilla, and red velvet cupcakes with buttercream swirls. Ideal as gifts or party favours — box of six, flavours may vary slightly by batch.',
                'ingredients' => 'Flour, cocoa, vanilla, cream cheese frosting, butter, eggs, sprinkles.',
                'message_on_cake_max_length' => null,
                'category' => 'cupcakes',
                'image_count' => 3,
                'image_tags' => 'cupcake,cake',
                'weights' => [250 => 449, 500 => 649],
                'is_highlight' => false,
                'is_trending' => true,
                'is_featured' => false,
                'show_on_homepage' => true,
                'homepage_sort_order' => 10,
                'flavors' => ['chocolate', 'vanilla', 'red-velvet'],
            ],
            [
                'name_en' => 'Pineapple Pastry (Box of 4)',
                'slug' => 'pineapple-pastry-box',
                'short_description' => 'Classic pineapple pastries. Soft and fruity.',
                'description_en' => 'Four pieces of our beloved pineapple pastry. Light, fluffy, and full of flavour — a tea-time staple since we opened.',
                'ingredients' => 'Puff pastry, pineapple filling, custard, sugar glaze.',
                'message_on_cake_max_length' => null,
                'category' => 'pastries-desserts',
                'image_count' => 2,
                'image_tags' => 'cake,pineapple',
                'weights' => [250 => 199],
                'is_highlight' => false,
                'is_trending' => false,
                'is_featured' => false,
                'show_on_homepage' => true,
                'homepage_sort_order' => 11,
                'flavors' => ['pineapple'],
            ],
            [
                'name_en' => 'Festive Dry Fruit Cake',
                'name_hi' => 'त्योहारी ड्राई फ्रूट केक',
                'slug' => 'festive-dry-fruit-cake',
                'short_description' => 'Rich fruit cake with nuts and spices. Seasonal special.',
                'description_en' => 'A dense, spiced cake loaded with dry fruits and nuts. Perfect for Diwali and Christmas — soaked overnight for depth of flavour.',
                'ingredients' => 'Mixed dry fruits, rum-soaked raisins, nuts, spices, flour, brown sugar, eggs.',
                'message_on_cake_max_length' => 30,
                'category' => 'seasonal-specials',
                'image_count' => 3,
                'image_tags' => 'fruitcake,cake',
                'weights' => [500 => 1099, 1000 => 1999, 2000 => 3499],
                'is_highlight' => false,
                'is_trending' => true,
                'is_featured' => true,
                'show_on_homepage' => true,
                'homepage_sort_order' => 12,
                'flavors' => ['butterscotch', 'pineapple'],
            ],
        ];

        foreach ($items as $item) {
            $category = $categories->get($item['category']) ?? $defaultCategory;
            $startingPrice = $this->minimumWeightPrice($item['weights'] ?? []);

            $product = Product::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'category_id' => $category->id,
                    'name_en' => $item['name_en'],
                    'name_hi' => $item['name_hi'] ?? null,
                    'name_gu' => $item['name_gu'] ?? null,
                    'short_description' => $item['short_description'],
                    'description_en' => $item['description_en'] ?? $item['short_description'],
                    'description_hi' => null,
                    'description_gu' => null,
                    'ingredients' => $item['ingredients'] ?? null,
                    'message_on_cake_max_length' => $item['message_on_cake_max_length'] ?? null,
                    'price' => $startingPrice,
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

            $this->seedProductImages(
                $product,
                $item['slug'],
                $imageCount,
                $item['image_tags'] ?? 'cake'
            );
            $this->syncProductFlavors($product, $item['flavors'] ?? []);
            $this->syncProductWeightVariants($product, $item['weights'] ?? []);
        }
    }

    /**
     * @param  array<int, float|int>  $weightsByGrams
     */
    private function minimumWeightPrice(array $weightsByGrams): float
    {
        if ($weightsByGrams === []) {
            return 0;
        }

        return (float) min($weightsByGrams);
    }

    /**
     * @param  array<int, float|int>  $weightsByGrams
     */
    private function syncProductWeightVariants(Product $product, array $weightsByGrams): void
    {
        if ($weightsByGrams === [] || $this->weightValuesByGrams->isEmpty()) {
            return;
        }

        ksort($weightsByGrams);

        $rows = [];
        foreach ($weightsByGrams as $grams => $price) {
            $value = $this->weightValuesByGrams->get((int) $grams);
            if (! $value) {
                $this->command?->warn("Weight {$grams}g not found in variant options; skipping for [{$product->slug}].");

                continue;
            }

            $rows[] = [
                'variant_option_value_id' => $value->id,
                'price' => (float) $price,
            ];
        }

        if ($rows === []) {
            return;
        }

        app(ProductVariantService::class)->syncVariants($product, $rows);
    }

    private function syncProductFlavors(Product $product, array $flavorSlugs): void
    {
        if ($flavorSlugs === [] || Flavor::count() === 0) {
            $product->flavors()->sync([]);

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

    private function seedProductImages(Product $product, string $slug, int $count, string $imageTags): void
    {
        $count = max(1, min($count, 10));
        $existing = $product->getMedia('product_images')->count();

        if ($existing >= $count) {
            return;
        }

        $tags = $this->normalizeCakeImageTags($imageTags);
        $startIndex = $existing;
        $newMediaIds = $product->getMedia('product_images')->sortBy('order_column')->pluck('id')->all();

        for ($index = $startIndex; $index < $count; $index++) {
            $media = $this->attachCakeImageFromUrl($product, $slug, $tags, $index);

            if ($media) {
                $newMediaIds[] = $media->id;

                continue;
            }

            $fallbackLock = self::CAKE_FALLBACK_LOCKS[$index % count(self::CAKE_FALLBACK_LOCKS)];
            $fallbackUrl = self::CAKE_IMAGE_BASE.'/cake?lock='.$fallbackLock;
            $media = $this->attachCakeImageFromUrl($product, $slug, 'cake', $index, $fallbackUrl);

            if ($media) {
                $newMediaIds[] = $media->id;
            }
        }

        if ($newMediaIds !== []) {
            Media::setNewOrder($newMediaIds);
        }
    }

    /**
     * Keep Flickr tags cake-specific: require a cake-family tag and drop ambiguous keywords.
     */
    private function normalizeCakeImageTags(string $imageTags): string
    {
        $raw = preg_replace('/[^a-z0-9,_-]/i', '', strtolower($imageTags)) ?: '';
        $parts = array_values(array_filter(explode(',', $raw)));
        $parts = array_values(array_diff($parts, self::BLOCKED_IMAGE_TAGS));

        $hasCakeFamily = collect($parts)->contains(
            fn (string $tag) => in_array($tag, self::CAKE_FAMILY_TAGS, true)
        );

        if (! $hasCakeFamily) {
            array_unshift($parts, 'cake');
        }

        if (! in_array('cake', $parts, true)) {
            array_unshift($parts, 'cake');
        } else {
            $parts = array_values(array_unique(array_merge(['cake'], array_diff($parts, ['cake']))));
        }

        return implode(',', array_slice($parts, 0, 3)) ?: 'cake';
    }

    private function cakeImageUrl(string $slug, string $tags, int $index, ?string $overrideUrl = null): string
    {
        if ($overrideUrl !== null) {
            return $overrideUrl;
        }

        $lock = abs(crc32($slug.'|'.$tags.'|'.$index));

        return self::CAKE_IMAGE_BASE.'/'.$tags.'?lock='.$lock;
    }

    private function attachCakeImageFromUrl(
        Product $product,
        string $slug,
        string $tags,
        int $index,
        ?string $overrideUrl = null
    ): ?Media {
        $url = $this->cakeImageUrl($slug, $tags, $index, $overrideUrl);

        try {
            return $product->addMediaFromUrl($url)
                ->toMediaCollection('product_images');
        } catch (\Throwable $e) {
            $this->command?->warn("Could not add cake image for product [{$product->slug}] (#{$index}): {$e->getMessage()}");

            return null;
        }
    }
}

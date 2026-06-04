<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductImageService
{
    public const MAX_IMAGES = 10;

    public const IMAGE_REF_PATTERN = '/^(existing:\d+|temp:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$/i';

    public function __construct(
        private ProductImageTempService $tempService
    ) {}

    public function syncFromRequest(Product $product, Request $request, User $user): void
    {
        if (! $request->hasAny(['product_images', 'removed_media_ids'])) {
            return;
        }

        $this->syncImages($product, [
            'product_images' => $request->input('product_images', []),
            'primary_image' => $request->input('primary_image'),
            'removed_media_ids' => $request->input('removed_media_ids', []),
        ], $user);
    }

    /**
     * @param  array{product_images?: array<int, string>, primary_image?: ?string, removed_media_ids?: array<int, int|string>}  $payload
     */
    public function syncImages(Product $product, array $payload, User $user): void
    {
        $orderedRefs = array_values(array_filter(
            $payload['product_images'] ?? [],
            fn ($ref) => is_string($ref) && preg_match(self::IMAGE_REF_PATTERN, $ref)
        ));

        if (count($orderedRefs) > self::MAX_IMAGES) {
            throw ValidationException::withMessages([
                'product_images' => [__('You may upload at most :max images per product.', ['max' => self::MAX_IMAGES])],
            ]);
        }

        $primaryRef = $payload['primary_image'] ?? null;
        if ($orderedRefs !== [] && $primaryRef) {
            $orderedRefs = $this->applyPrimaryFirst($orderedRefs, $primaryRef);
        }

        $removedIds = collect($payload['removed_media_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($removedIds !== []) {
            $product->getMedia('product_images')
                ->whereIn('id', $removedIds)
                ->each(fn (Media $media) => $media->delete());
        }

        $finalMediaIds = [];

        foreach ($orderedRefs as $ref) {
            if (str_starts_with($ref, 'existing:')) {
                $mediaId = (int) substr($ref, 9);
                $media = $product->getMedia('product_images')->firstWhere('id', $mediaId);
                if (! $media) {
                    throw ValidationException::withMessages([
                        'product_images' => [__('One or more existing images are invalid.')],
                    ]);
                }
                $finalMediaIds[] = $media->id;
            } else {
                $token = substr($ref, 5);
                try {
                    $path = $this->tempService->consume($token, $user);
                } catch (RuntimeException $e) {
                    throw ValidationException::withMessages([
                        'product_images' => [$e->getMessage()],
                    ]);
                }
                $media = $product->addMedia($path)->toMediaCollection('product_images');
                $finalMediaIds[] = $media->id;
            }
        }

        if ($finalMediaIds !== []) {
            Media::setNewOrder($finalMediaIds);
        }
    }

    /**
     * @param  array<int, string>  $refs
     * @return array<int, string>
     */
    private function applyPrimaryFirst(array $refs, string $primaryRef): array
    {
        $index = array_search($primaryRef, $refs, true);
        if ($index === false) {
            return $refs;
        }

        $primary = $refs[$index];
        unset($refs[$index]);

        return array_values(array_merge([$primary], $refs));
    }
}

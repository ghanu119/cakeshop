<?php

namespace App\Services;

use App\Models\Slider;
use App\Models\SliderItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SliderItemService
{
    public const IMAGE_REF_PATTERN = '/^(existing:\d+|temp:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$/i';

    public function __construct(
        private SliderItemImageTempService $tempService
    ) {}

    public function listForSlider(Slider $slider, Request $request): LengthAwarePaginator
    {
        $query = $slider->items()->with('media');

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('link', 'like', "%{$term}%")
                    ->orWhere('video_url', 'like', "%{$term}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        return $query->orderBy('sort_order')->orderBy('id')->paginate(15)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createOrUpdate(Slider $slider, ?SliderItem $item, array $data, User $user): SliderItem
    {
        $isCreate = $item === null;
        $type = $data['type'] ?? $item?->type ?? SliderItem::TYPE_IMAGE;

        return DB::transaction(function () use ($slider, $item, $data, $user, $isCreate, $type) {
            $item = $item ?? new SliderItem;
            $item->slider_id = $slider->id;
            $item->type = $type;
            $item->title = $this->nullableString($data, 'title', $isCreate ? null : $item->title);
            $item->link = $this->nullableString($data, 'link', $isCreate ? null : $item->link);
            $item->sort_order = (int) ($data['sort_order'] ?? $item->sort_order ?? 0);
            $item->is_active = ! empty($data['is_active']);

            if ($type === SliderItem::TYPE_VIDEO) {
                $item->video_url = $this->nullableString($data, 'video_url', $isCreate ? null : $item->video_url);
                if ($item->hasImage()) {
                    $item->clearMediaCollection('slide_image');
                }
            } else {
                $item->video_url = null;
            }

            $item->save();

            if ($type === SliderItem::TYPE_IMAGE) {
                $this->syncSlideImage(
                    $item,
                    $data['slide_image_ref'] ?? null,
                    $user,
                    ! empty($data['remove_slide_image']),
                    $isCreate
                );
            }

            return $item->fresh(['media']);
        });
    }

    public function syncSlideImage(
        SliderItem $item,
        ?string $imageRef,
        User $user,
        bool $removeExisting = false,
        bool $isCreate = false
    ): void {
        if ($removeExisting && ! $imageRef) {
            throw ValidationException::withMessages([
                'slide_image_ref' => [__('Please upload a slide image.')],
            ]);
        }

        if ($removeExisting && $item->hasImage()) {
            $item->clearMediaCollection('slide_image');
        }

        if ($imageRef === null || $imageRef === '') {
            if ($isCreate || ! $item->hasImage()) {
                throw ValidationException::withMessages([
                    'slide_image_ref' => [__('Please upload a slide image.')],
                ]);
            }

            return;
        }

        if (! preg_match(self::IMAGE_REF_PATTERN, $imageRef)) {
            throw ValidationException::withMessages([
                'slide_image_ref' => [__('The slide image is invalid. Please upload again.')],
            ]);
        }

        if (str_starts_with($imageRef, 'existing:')) {
            $mediaId = (int) substr($imageRef, 9);
            $media = $item->slideImageMedia();
            if (! $media || $media->id !== $mediaId) {
                throw ValidationException::withMessages([
                    'slide_image_ref' => [__('The slide image is invalid. Please upload again.')],
                ]);
            }

            return;
        }

        $token = substr($imageRef, 5);
        try {
            $path = $this->tempService->consume($token, $user);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages([
                'slide_image_ref' => [$e->getMessage()],
            ]);
        }

        $item->clearMediaCollection('slide_image');
        $item->addMedia($path)->toMediaCollection('slide_image');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function nullableString(array $data, string $key, ?string $current = null): ?string
    {
        if (! array_key_exists($key, $data)) {
            return $current;
        }

        return filled($data[$key]) ? (string) $data[$key] : null;
    }
}

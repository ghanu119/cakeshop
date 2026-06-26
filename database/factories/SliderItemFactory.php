<?php

namespace Database\Factories;

use App\Models\Slider;
use App\Models\SliderItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends Factory<SliderItem>
 */
class SliderItemFactory extends Factory
{
    protected $model = SliderItem::class;

    public function definition(): array
    {
        return [
            'slider_id' => Slider::factory(),
            'type' => SliderItem::TYPE_IMAGE,
            'title' => fake()->sentence(3),
            'link' => fake()->optional()->url(),
            'video_url' => null,
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }

    public function withImage(): static
    {
        return $this->afterCreating(function (SliderItem $item) {
            if ($item->type !== SliderItem::TYPE_IMAGE) {
                return;
            }

            $item->addMedia(UploadedFile::fake()->image('slide.jpg', SliderItem::SLIDE_WIDTH, SliderItem::SLIDE_HEIGHT))
                ->toMediaCollection('slide_image');
        });
    }

    public function video(?string $url = null): static
    {
        return $this->state(fn () => [
            'type' => SliderItem::TYPE_VIDEO,
            'video_url' => $url ?? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}

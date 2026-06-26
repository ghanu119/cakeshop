<?php

namespace Database\Seeders;

use App\Models\Slider;
use App\Models\SliderItem;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SliderItemSeeder extends Seeder
{
    public function run(): void
    {
        $homeSlider = Slider::query()->firstOrCreate(
            ['slug' => Slider::SLUG_HOME],
            [
                'name' => 'Home Slider',
                'description' => 'Homepage hero carousel for Better Buns theme',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $slides = [
            [
                'title' => 'Sweetness Redefined',
                'link' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Fresh Daily',
                'link' => '/products',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($slides as $index => $data) {
            $item = SliderItem::firstOrCreate(
                [
                    'slider_id' => $homeSlider->id,
                    'sort_order' => $data['sort_order'],
                ],
                array_merge($data, [
                    'slider_id' => $homeSlider->id,
                    'type' => SliderItem::TYPE_IMAGE,
                ])
            );

            if ($item->hasImage()) {
                continue;
            }

            $image = UploadedFile::fake()->image('slide-'.$index.'.jpg', SliderItem::SLIDE_WIDTH, SliderItem::SLIDE_HEIGHT);
            $path = $image->store('seeders/slider-items', 'public');
            $item->addMedia(Storage::disk('public')->path($path))
                ->preservingOriginal()
                ->toMediaCollection('slide_image');
        }
    }
}

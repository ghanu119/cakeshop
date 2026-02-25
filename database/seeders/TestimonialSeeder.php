<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'customer_name' => 'Rahul Sharma',
                'customer_initials' => 'RS',
                'review' => 'The best chocolate cake I have ever tasted! The quality is outstanding and the delivery was super fast. Highly recommended!',
                'rating' => 5,
                'is_verified' => true,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'customer_name' => 'Priya Kumar',
                'customer_initials' => 'PK',
                'review' => 'Ordered a custom birthday cake and it exceeded all expectations. Beautiful design and delicious taste. Will definitely order again!',
                'rating' => 5,
                'is_verified' => true,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'customer_name' => 'Amit Mehta',
                'customer_initials' => 'AM',
                'review' => 'Amazing service! The cake was fresh, moist, and perfectly decorated. Great value for money. Thank you for making our celebration special!',
                'rating' => 5,
                'is_verified' => true,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'customer_name' => 'Neha Patel',
                'customer_initials' => 'NP',
                'review' => 'Ordered the Black Forest cake for my daughter\'s birthday. It was a hit! Everyone asked where we got it from. Will definitely order again.',
                'rating' => 5,
                'is_verified' => true,
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'customer_name' => 'Vikram Singh',
                'customer_initials' => 'VS',
                'review' => 'Fast delivery and the cake was still cold and fresh. The packaging was very secure. Highly recommend for any occasion.',
                'rating' => 5,
                'is_verified' => false,
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            Testimonial::firstOrCreate(
                ['customer_name' => $item['customer_name'], 'review' => $item['review']],
                $item
            );
        }
    }
}

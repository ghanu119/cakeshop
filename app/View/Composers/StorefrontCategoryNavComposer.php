<?php

namespace App\View\Composers;

use App\Models\Category;
use Illuminate\View\View;

class StorefrontCategoryNavComposer
{
    public function compose(View $view): void
    {
        if (! in_array(storefront_theme_key(), ['warm', 'better-buns'], true)) {
            $view->with('navCategories', collect());

            return;
        }

        $view->with(
            'navCategories',
            Category::query()->active()->orderBy('sort_order')->get()
        );
    }
}

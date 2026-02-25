<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default theme
    |--------------------------------------------------------------------------
    | Used when no theme is set in Admin → Settings. Must be a key from 'available'.
    */
    'default' => env('THEME_DEFAULT', 'warm'),

    /*
    |--------------------------------------------------------------------------
    | Available themes
    |--------------------------------------------------------------------------
    | Keys are used in Admin → Settings and map to hexadog themes:
    | warm → cakeshop/warm, lumiere → cakeshop/lumiere. Each theme has its own
    | layout, CSS, and assets under themes/cakeshop/{key}/ (no overlap).
    */
    'available' => [
        'warm' => [
            'name' => 'Warm (Amber)',
            'description' => 'Default warm amber/orange cake shop style',
        ],
        'lumiere' => [
            'name' => 'Lumiere (Elegant)',
            'description' => 'Elegant beige, olive & gold – artisan bakery style',
        ],
    ],

];

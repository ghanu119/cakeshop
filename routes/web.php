<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactEnquiryController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\FlavorController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Kitchen\OrderController as KitchenOrderController;
use App\Http\Controllers\CategoryController as FrontendCategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController as FrontendProductController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('contact', [ContactController::class, 'index'])->name('contact.index');
Route::post('contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('about', [PageController::class, 'about'])->name('about');
Route::get('ingredients', [PageController::class, 'ingredients'])->name('ingredients');
Route::get('terms', [PageController::class, 'terms'])->name('terms');
Route::get('privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('cookie-policy', [PageController::class, 'cookiePolicy'])->name('cookie-policy');

Route::get('products', [FrontendProductController::class, 'index'])->name('products.index');
Route::get('products/{slug}', [FrontendProductController::class, 'show'])->name('products.show');
Route::get('categories/{slug}', [FrontendCategoryController::class, 'show'])->name('categories.show');

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('robots.txt', [\App\Http\Controllers\RobotsController::class, 'index'])->name('robots');

Route::prefix('order')->name('order.')->group(function () {
    Route::get('product/{product}', [OrderController::class, 'placeForm'])->name('place');
    Route::post('product/{product}', [OrderController::class, 'place'])->name('store');
    Route::get('confirm/{order}', [OrderController::class, 'confirm'])->name('confirm');
    Route::get('history', [OrderController::class, 'historyForm'])->name('history');
    Route::post('history', [OrderController::class, 'historySearch'])->name('history.search');
    Route::get('submit-payment/{order}', [OrderController::class, 'submitPaymentForm'])->name('submit-payment');
    Route::get('submit-payment', [OrderController::class, 'submitPaymentForm'])->name('submit-payment.enter');
    Route::post('submit-payment', [OrderController::class, 'submitPaymentLookup'])->name('submit-payment.lookup');
    Route::post('submit-payment/{order}', [OrderController::class, 'submitPayment'])->name('submit-payment.store');
});

Route::middleware(['auth'])->group(function () {
    Route::post('logout', [LogoutController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'verified', 'role:Admin|Kitchen'])->group(function () {
    // Admin area: all backend routes under /admin prefix
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::middleware(['permission:orders.view'])->group(function () {
            Route::get('kitchen/orders', [KitchenOrderController::class, 'index'])->name('kitchen.orders.index');
            Route::get('kitchen/orders/{order}', [KitchenOrderController::class, 'show'])->name('kitchen.orders.show');
        });
        Route::middleware(['permission:orders.update'])->group(function () {
            Route::post('kitchen/orders/{order}/update-status', [KitchenOrderController::class, 'updateStatus'])->name('kitchen.orders.update-status');
        });
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('flavors', FlavorController::class)->except(['show']);
        Route::post('products/images/temp', [\App\Http\Controllers\Admin\ProductImageTempController::class, 'store'])
            ->name('products.images.temp.store');
        Route::delete('products/images/temp/{token}', [\App\Http\Controllers\Admin\ProductImageTempController::class, 'destroy'])
            ->name('products.images.temp.destroy');
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)->except(['show']);
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::middleware(['permission:settings.manage'])->group(function () {
            Route::resource('cake-weights', \App\Http\Controllers\Admin\CakeWeightController::class)
                ->parameters(['cake-weights' => 'cake_weight'])
                ->except(['show']);
            Route::redirect('variant-option-types', '/admin/cake-weights')->name('variant-option-types.index');
            Route::redirect('variant-option-types/create', '/admin/cake-weights/create')->name('variant-option-types.create');
            Route::get('variant-option-types/{variant_option_type}/values', fn () => redirect()->route('admin.cake-weights.index'));
        });
        Route::resource('features', FeatureController::class)->except(['show']);
        Route::resource('testimonials', TestimonialController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
        Route::middleware('role:Admin')->group(function () {
            Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::post('orders/{order}/verify-payment', [AdminOrderController::class, 'verifyPayment'])->name('orders.verify-payment');
            Route::post('orders/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        });
        Route::get('contact-enquiries', [ContactEnquiryController::class, 'index'])->name('contact-enquiries.index');
        Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    });
});

// Redirect legacy /dashboard to /admin/dashboard so admin prefix is required
Route::get('dashboard', fn () => redirect()->route('admin.dashboard', [], 301))->name('dashboard')->middleware(['auth', 'verified', 'role:Admin|Kitchen']);

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Admin login (separate from frontend login; only Admin|Kitchen may use this)
Route::prefix('admin')->middleware('guest')->group(function () {
    Route::get('login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'login'])->name('admin.login.post');
});

require __DIR__.'/auth.php';

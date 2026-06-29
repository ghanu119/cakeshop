<?php

use App\Http\Controllers\Account\AuthController as AccountAuthController;
use App\Http\Controllers\Account\DashboardController as AccountDashboardController;
use App\Http\Controllers\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Account\ProfileController as AccountProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactEnquiryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\FlavorController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\SliderItemController;
use App\Http\Controllers\Admin\SliderItemImageTempController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ServiceablePincodeController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Kitchen\OrderController as KitchenOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PincodeCheckController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController as FrontendProductController;
use App\Http\Controllers\SitemapController;
use App\Support\AuthGuards;
use Illuminate\Support\Facades\Route;

Route::bind('customer', function (string $value) {
    $user = \App\Models\User::withTrashed()->role('Customer', AuthGuards::STAFF)->find($value);

    if (! $user) {
        abort(404);
    }

    return $user;
});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('contact', [ContactController::class, 'index'])->name('contact.index');
Route::post('contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('about', [PageController::class, 'about'])->name('about');
Route::get('ingredients', [PageController::class, 'ingredients'])->name('ingredients');
Route::get('terms', [PageController::class, 'terms'])->name('terms');
Route::get('privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('cookie-policy', [PageController::class, 'cookiePolicy'])->name('cookie-policy');

Route::get('products', [FrontendProductController::class, 'index'])->name('products.index');
Route::get('products/{slug}', [FrontendProductController::class, 'index'])->name('products.category');
Route::get('product/{slug}', [FrontendProductController::class, 'show'])->name('product.show');
Route::get('categories/{slug}', function (string $slug) {
    return redirect()->route('products.category', array_merge(
        ['slug' => $slug],
        request()->query()
    ), 301);
})->name('categories.show');

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('robots.txt', [\App\Http\Controllers\RobotsController::class, 'index'])->name('robots');

Route::prefix('account')->name('account.')->middleware(['account.guest'])->group(function () {
    Route::middleware('guest:'.App\Support\AuthGuards::CUSTOMER)->group(function () {
        Route::get('login', [AccountAuthController::class, 'redirectToAuthModal'])->name('login');
        Route::get('verify-otp', [AccountAuthController::class, 'redirectToAuthModal'])->name('verify-otp');
        Route::get('register', [AccountAuthController::class, 'redirectToAuthModal'])->name('register');
    });

    Route::middleware(['customer.session'])->group(function () {
        Route::get('/', [AccountDashboardController::class, 'index'])->name('dashboard');
        Route::get('orders', [AccountOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AccountOrderController::class, 'show'])->name('orders.show');
        Route::get('profile', [AccountProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [AccountProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile', [AccountProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('logout', [AccountAuthController::class, 'logout'])->name('logout');
    });
});

Route::prefix('order')->name('order.')->group(function () {
    Route::get('product/{product:slug}', [OrderController::class, 'placeForm'])->name('place');
    Route::post('checkout/send-otp', [OrderController::class, 'sendCheckoutOtp'])->middleware('throttle:60,1')->name('checkout.send-otp');
    Route::post('checkout/verify-otp', [OrderController::class, 'verifyCheckoutOtp'])->middleware('throttle:10,15')->name('checkout.verify-otp');
    Route::post('product/{product:slug}/validate-coupon', [OrderController::class, 'validateCoupon'])->middleware('throttle:60,1')->name('product.validate-coupon');
    Route::post('product/{product:slug}', [OrderController::class, 'place'])->name('store');
    Route::middleware(['throttle:30,1'])->post('check-pincode', [PincodeCheckController::class, 'check'])->name('pincode.check');
    Route::get('confirm/{order}', [OrderController::class, 'confirm'])->name('confirm');
    Route::get('payment-qr/download', [OrderController::class, 'downloadPaymentQr'])->name('payment-qr.download');
    Route::get('history', [OrderController::class, 'historyForm'])->name('history');
    Route::post('history', [OrderController::class, 'historySearch'])->name('history.search');
    Route::get('submit-payment/{order}', [OrderController::class, 'submitPaymentForm'])->name('submit-payment');
    Route::get('submit-payment', [OrderController::class, 'submitPaymentForm'])->name('submit-payment.enter');
    Route::post('submit-payment', [OrderController::class, 'submitPaymentLookup'])->name('submit-payment.lookup');
    Route::post('submit-payment/{order}', [OrderController::class, 'submitPayment'])->name('submit-payment.store');
});

Route::middleware(['auth:web'])->group(function () {
    Route::post('logout', [LogoutController::class, 'destroy'])->name('logout');
});

Route::middleware(['ensure.admin.https', 'auth:web', 'verified', 'role:Admin|Kitchen'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::middleware(['permission:orders.view'])->group(function () {
            Route::get('kitchen/orders/upcoming', [KitchenOrderController::class, 'upcomingIndex'])->name('kitchen.orders.upcoming');
            Route::get('kitchen/orders/upcoming/{order}', [KitchenOrderController::class, 'upcomingShow'])->name('kitchen.orders.upcoming.show');
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
        Route::post('settings/test-pusher', [SettingsController::class, 'testPusher'])->name('settings.test-pusher');
        Route::middleware(['throttle:60,1'])->group(function () {
            Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
            Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
            Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        });
        Route::middleware(['throttle:30,1'])->group(function () {
            Route::get('push-subscriptions/status', [NotificationController::class, 'pushSubscriptionStatus'])->name('push-subscriptions.status');
            Route::post('push-subscriptions', [NotificationController::class, 'storePushSubscription'])->name('push-subscriptions.store');
            Route::post('push-subscriptions/test', [NotificationController::class, 'testPushNotification'])->name('push-subscriptions.test');
            Route::delete('push-subscriptions', [NotificationController::class, 'destroyPushSubscription'])->name('push-subscriptions.destroy');
        });
        Route::middleware(['permission:settings.manage'])->group(function () {
            Route::resource('cake-weights', \App\Http\Controllers\Admin\CakeWeightController::class)
                ->parameters(['cake-weights' => 'cake_weight'])
                ->except(['show']);
            Route::resource('serviceable-pincodes', ServiceablePincodeController::class)->except(['show']);
            Route::redirect('variant-option-types', '/admin/cake-weights')->name('variant-option-types.index');
            Route::redirect('variant-option-types/create', '/admin/cake-weights/create')->name('variant-option-types.create');
            Route::get('variant-option-types/{variant_option_type}/values', fn () => redirect()->route('admin.cake-weights.index'));
        });
        Route::resource('features', FeatureController::class)->except(['show']);
        Route::resource('coupons', CouponController::class)->except(['show']);
        Route::get('sliders', [SliderController::class, 'index'])->name('sliders.index');
        Route::patch('sliders/{slider}', [SliderController::class, 'update'])->name('sliders.update');
        Route::post('sliders/items/images/temp', [SliderItemImageTempController::class, 'store'])
            ->name('sliders.items.images.temp.store');
        Route::delete('sliders/items/images/temp/{token}', [SliderItemImageTempController::class, 'destroy'])
            ->name('sliders.items.images.temp.destroy');
        Route::resource('sliders.items', SliderItemController::class)
            ->except(['show'])
            ->parameters(['sliders' => 'slider', 'items' => 'item']);
        Route::redirect('home-sliders', '/admin/sliders')->name('home-sliders.index');
        Route::redirect('home-sliders/create', '/admin/sliders')->name('home-sliders.create');
        Route::redirect('home_sliders', '/admin/sliders');
        Route::resource('testimonials', TestimonialController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
        Route::middleware('role:Admin')->group(function () {
            Route::get('customers/lookup', [CustomerController::class, 'lookup'])->name('customers.lookup');
            Route::post('customers/{customer}/impersonate', [CustomerController::class, 'impersonate'])->name('customers.impersonate');
            Route::post('impersonation/stop', [ImpersonationController::class, 'stop'])->name('impersonation.stop');
            Route::resource('customers', CustomerController::class)->except(['edit', 'update']);
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

Route::get('dashboard', fn () => redirect()->route('admin.dashboard', [], 301))->name('dashboard')->middleware(['auth:web', 'verified', 'role:Admin|Kitchen']);

Route::get('profile', function () {
    if (auth(App\Support\AuthGuards::CUSTOMER)->check()) {
        return redirect()->route('account.profile.edit');
    }

    $staff = auth(App\Support\AuthGuards::STAFF)->user();

    if ($staff?->hasAnyRole(['Admin', 'Kitchen'])) {
        return redirect()->route('admin.profile.edit');
    }

    return redirect()->route('home', ['auth' => 1]);
})->middleware(['auth:web,customer'])->name('profile');

Route::prefix('admin')->middleware(['ensure.admin.https', 'admin.guest'])->group(function () {
    Route::get('login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'login'])->name('admin.login.post');
});

require __DIR__.'/auth.php';

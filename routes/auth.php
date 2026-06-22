<?php

use Illuminate\Support\Facades\Route;

Route::redirect('login', '/?auth=1')->name('login');
Route::redirect('register', '/?auth=1')->name('register');

Route::middleware('auth')->group(function () {
    Route::get('verify-email', fn () => redirect()->route('account.dashboard'))
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', \App\Http\Controllers\Auth\VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::get('confirm-password', function () {
        if (auth(App\Support\AuthGuards::CUSTOMER)->check()) {
            return redirect()->route('account.dashboard');
        }

        return redirect()->route('admin.dashboard');
    })->middleware('auth:web,customer')->name('password.confirm');
});

Route::middleware('guest')->group(function () {
    Route::redirect('forgot-password', '/?auth=1')->name('password.request');
    Route::redirect('reset-password/{token}', '/?auth=1')->name('password.reset');
});

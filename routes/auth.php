<?php

use Illuminate\Support\Facades\Route;

Route::redirect('login', '/account/login')->name('login');
Route::redirect('register', '/account/login')->name('register');

Route::middleware('auth')->group(function () {
    Route::get('verify-email', fn () => redirect()->route('account.dashboard'))
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', \App\Http\Controllers\Auth\VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::get('confirm-password', fn () => redirect()->route('admin.dashboard'))
        ->name('password.confirm');
});

Route::middleware('guest')->group(function () {
    Route::redirect('forgot-password', '/admin/login')->name('password.request');
    Route::redirect('reset-password/{token}', '/admin/login')->name('password.reset');
});

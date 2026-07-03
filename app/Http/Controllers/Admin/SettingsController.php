<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Setting;
use App\Models\SiteSetting;
use App\Services\Payments\PaymentSettingsResolver;
use App\Services\PusherSettingsResolver;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function __construct(
        private SettingsService $settingsService,
        private PusherSettingsResolver $pusherSettingsResolver,
        private PaymentSettingsResolver $paymentSettingsResolver,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);
        $settings = Setting::allCached();
        $siteSetting = SiteSetting::firstOrCreate([]);

        $razorpayKeyIdSaved = Setting::hasEncryptedValue('razorpay_key_id');
        $razorpayKeySecretSaved = Setting::hasEncryptedValue('razorpay_key_secret');
        $razorpayConfigured = Setting::isRazorpayConfigured();
        $paymentGateway = Setting::getPaymentGateway();
        $razorpayKeyIdMasked = Setting::maskedEncryptedValue('razorpay_key_id', 5);
        $razorpayKeySecretMasked = Setting::maskedEncryptedValue('razorpay_key_secret', 4);
        $onlinePaymentReady = $paymentGateway === 'razorpay' && $razorpayConfigured;

        return view('admin.settings.index', compact(
            'settings',
            'siteSetting',
            'razorpayKeyIdSaved',
            'razorpayKeySecretSaved',
            'razorpayConfigured',
            'paymentGateway',
            'razorpayKeyIdMasked',
            'razorpayKeySecretMasked',
            'onlinePaymentReady',
        ));
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $this->settingsService->updateFromRequest($request);

        $siteSetting = SiteSetting::firstOrCreate([]);
        if ($request->hasFile('payment_qr')) {
            $siteSetting->clearMediaCollection('payment_qr');
            $siteSetting->addMediaFromRequest('payment_qr')->toMediaCollection('payment_qr');
        }
        if ($request->hasFile('header_icon')) {
            $siteSetting->clearMediaCollection('header_icon');
            $siteSetting->addMediaFromRequest('header_icon')->toMediaCollection('header_icon');
        }

        return redirect()->route('admin.settings.index')->with('status', __('Settings saved.'));
    }

    public function testPusher(): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        try {
            $result = $this->pusherSettingsResolver->testConnection();

            return $result['success']
                ? ApiResponse::success($result, $result['message'])
                : ApiResponse::error($result['message'], 422);
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error(
                __('Could not connect to Pusher. Please check your credentials and try again.'),
                500
            );
        }
    }

    public function testRazorpay(): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        try {
            $result = $this->paymentSettingsResolver->testRazorpayConnection();

            return $result['success']
                ? ApiResponse::success($result, $result['message'])
                : ApiResponse::error($result['message'], 422);
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error(
                __('Could not connect to Razorpay. Please check your credentials and try again.'),
                500
            );
        }
    }
}

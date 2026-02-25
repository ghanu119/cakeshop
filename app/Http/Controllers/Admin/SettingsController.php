<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use App\Models\SiteSetting;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private SettingsService $settingsService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);
        $settings = Setting::allCached();
        $siteSetting = SiteSetting::firstOrCreate([]);

        return view('admin.settings.index', compact('settings', 'siteSetting'));
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
}

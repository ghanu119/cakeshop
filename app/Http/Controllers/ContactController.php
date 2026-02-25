<?php

namespace App\Http\Controllers;

use App\Services\ContactEnquiryService;
use App\Http\Requests\StoreContactEnquiryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(
        private ContactEnquiryService $contactEnquiryService
    ) {}

    public function index(): View
    {
        // Show map on contact page only when admin has set it in Settings (Contact page map).
        $googleMapIframe = settings('google_map_iframe');
        $googleMapIframe = is_string($googleMapIframe) ? trim($googleMapIframe) : '';

        return view('contact.index', compact('googleMapIframe'));
    }

    public function store(StoreContactEnquiryRequest $request): RedirectResponse
    {
        $this->contactEnquiryService->store($request->validated());

        return redirect()->route('contact.index')->with('status', __('Thank you. We have received your message and will get back to you soon.'));
    }
}

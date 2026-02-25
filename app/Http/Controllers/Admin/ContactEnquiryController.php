<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContactEnquiryService;
use Illuminate\View\View;

class ContactEnquiryController extends Controller
{
    public function __construct(
        private ContactEnquiryService $contactEnquiryService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', \App\Models\ContactEnquiry::class);
        $enquiries = $this->contactEnquiryService->listForAdmin(request());

        return view('admin.contact-enquiries.index', compact('enquiries'));
    }
}

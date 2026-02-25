<?php

namespace App\Services;

use App\Models\ContactEnquiry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ContactEnquiryService
{
    public function store(array $data): ContactEnquiry
    {
        $enquiry = new ContactEnquiry;
        $enquiry->name = $data['name'];
        $enquiry->email = $data['email'];
        $enquiry->phone = $data['phone'] ?? null;
        $enquiry->inquiry_type = $data['inquiry_type'] ?? null;
        $enquiry->subject = $data['subject'];
        $enquiry->message = $data['message'];
        $enquiry->save();

        $adminEmail = settings('admin_email');
        if ($adminEmail) {
            try {
                \Illuminate\Support\Facades\Mail::to($adminEmail)->send(
                    new \App\Mail\ContactEnquiryNotification($enquiry)
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $enquiry;
    }

    public function listForAdmin(Request $request): LengthAwarePaginator
    {
        $query = ContactEnquiry::query();

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%");
            });
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        return $query->orderByDesc('created_at')->paginate(15)->withQueryString();
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\User\RegisteredVia;
use App\Support\PhoneNormalizer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CustomerService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $query = User::customers()->withCount('orders');

        if ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        }

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        return $query->orderByDesc('created_at')->paginate(15)->withQueryString();
    }

    public function lookup(?string $email, ?string $phone): array
    {
        $emailMatch = null;
        $phoneMatch = null;

        if ($email !== null && trim($email) !== '') {
            $emailMatch = User::customers()->where('email', strtolower(trim($email)))->first();
        }

        if ($phone !== null && trim($phone) !== '') {
            $normalized = PhoneNormalizer::normalize($phone);
            if ($normalized !== null) {
                $phoneMatch = User::customers()->where('phone', $normalized)->first();
            }
        }

        if ($emailMatch && $phoneMatch && $emailMatch->id !== $phoneMatch->id) {
            return [
                'conflict' => true,
                'message' => __('Email and phone belong to different customers — fix one field.'),
                'email_match' => $this->formatLookupMatch($emailMatch),
                'phone_match' => $this->formatLookupMatch($phoneMatch),
            ];
        }

        $match = $emailMatch ?? $phoneMatch;

        return [
            'conflict' => false,
            'match' => $match ? $this->formatLookupMatch($match) : null,
        ];
    }

    public function create(array $data, User $admin): User
    {
        $user = new User;
        $user->name = $data['name'];
        $user->phone = PhoneNormalizer::normalize($data['phone']);
        $user->email = isset($data['email']) && $data['email'] !== '' ? strtolower(trim($data['email'])) : null;
        $user->email_verified_at = $user->email ? now() : null;
        $user->registered_via = RegisteredVia::ADMIN_CREATED;
        $user->created_by_admin_id = $admin->id;
        $user->password = null;
        $user->save();
        $user->assignRole('Customer');

        return $user;
    }

    public function ordersForCustomer(User $customer): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', $customer->id)
            ->with(['product', 'media'])
            ->orderByDesc('ordered_at')
            ->paginate(15);
    }

    public function ordersForCustomerAccount(User $customer, int $perPage = 10): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', $customer->id)
            ->orderByDesc('ordered_at')
            ->paginate($perPage);
    }

    public function recentOrdersForCustomer(User $customer, int $limit = 5)
    {
        return Order::query()
            ->where('user_id', $customer->id)
            ->orderByDesc('ordered_at')
            ->limit($limit)
            ->get();
    }

    private function formatLookupMatch(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'orders_count' => $user->orders()->count(),
            'created_at' => $user->created_at?->format('d M Y'),
            'view_url' => route('admin.customers.show', $user),
            'impersonate_url' => route('admin.customers.impersonate', $user),
        ];
    }
}

<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * List users with filters (search by name/email/phone, role).
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $query = User::query()->with('roles');

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        return $query->orderBy('name')->paginate(15)->withQueryString();
    }

    /**
     * Create or update user (no mass assignment).
     */
    public function createOrUpdate(User $user, array $data): User
    {
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (isset($data['phone'])) {
            $user->phone = $data['phone'];
        }
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user;
    }
}

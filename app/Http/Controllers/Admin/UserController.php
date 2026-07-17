<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);
        $users = $this->userService->list(request());
        $roles = $this->staffRoles();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);
        $roles = $this->staffRoles();

        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User;
        $this->userService->createOrUpdate($user, $request->validated());

        return redirect()->route('admin.users.index')->with('status', __('User created.'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);
        $user->load('roles');
        $roles = $this->staffRoles();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $this->userService->createOrUpdate($user, $data);

        return redirect()->route('admin.users.index')->with('status', __('User updated.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', __('User deleted.'));
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Role> */
    private function staffRoles()
    {
        return Role::where('guard_name', config('auth.defaults.guard'))
            ->whereNot('name', 'Customer')
            ->orderBy('name')
            ->get();
    }
}

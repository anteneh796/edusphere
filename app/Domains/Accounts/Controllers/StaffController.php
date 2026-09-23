<?php

namespace App\Domains\Accounts\Controllers;

use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Accounts\Requests\StoreStaffRequest;
use App\Domains\Accounts\Requests\UpdateStaffRequest;
use App\Domains\Accounts\Services\UserService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\RoleName;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    private const NON_STAFF_ROLES = [RoleName::Student->value, RoleName::Parent->value];

    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): View
    {
        $this->requirePermission('staff.view');

        $staff = User::query()
            ->with('roles')
            ->whereHas('roles', fn ($query) => $query->whereNotIn('roles.name', self::NON_STAFF_ROLES))
            ->when($request->filled('q'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), function ($query, $role) {
                $query->whereHas('roles', fn ($q) => $q->where('roles.name', $role));
            })
            ->when($request->filled('staff_type'), function ($query, $type) {
                $query->where('staff_type', $type);
            })
            ->when($request->filled('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $roles = $this->staffRoles();

        return view('staff.index', compact('staff', 'roles'));
    }

    public function create(): View
    {
        $this->requirePermission('staff.create');

        return view('staff.create', ['roleOptions' => $this->staffRoles()]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $this->requirePermission('staff.create');

        $user = $this->userService->create($request->validated(), $request->input('roles', []));
        ActivityLogger::log('created staff member '.$user->full_name, 'users', $user->id);

        return redirect()
            ->route('staff.show', $user)
            ->with('status', 'Staff member "'.$user->full_name.'" created successfully.');
    }

    public function show(User $user): View
    {
        $this->requirePermission('staff.view');

        return view('staff.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->requirePermission('staff.edit');

        return view('staff.edit', ['user' => $user, 'roleOptions' => $this->staffRoles()]);
    }

    public function update(UpdateStaffRequest $request, User $user): RedirectResponse
    {
        $this->requirePermission('staff.edit');

        $user = $this->userService->update($user, $request->validated(), $request->input('roles', []));
        ActivityLogger::log('updated staff member '.$user->full_name, 'users', $user->id);

        return redirect()
            ->route('staff.show', $user)
            ->with('status', 'Staff member "'.$user->full_name.'" updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->requirePermission('staff.edit');

        $this->userService->delete($user);
        ActivityLogger::log('deleted staff member '.$user->full_name, 'users', $user->id);

        return redirect()
            ->route('staff.index')
            ->with('status', 'Staff member "'.$user->full_name.'" deleted.');
    }

    private function staffRoles(): Collection
    {
        return Role::whereNotIn('name', self::NON_STAFF_ROLES)->orderBy('label')->get();
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}

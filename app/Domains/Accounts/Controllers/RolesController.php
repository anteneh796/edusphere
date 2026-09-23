<?php

namespace App\Domains\Accounts\Controllers;

use App\Domains\Accounts\Models\Permission;
use App\Domains\Accounts\Models\Role;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RolesController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('permissions', 'users')
            ->orderBy('label')
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $role->load('permissions');

        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $lock = config("rbac.roles.{$role->name}", []);
        $selected = array_values($request->input('permissions', []));

        if (! empty($lock)) {
            // Permanent roles keep their default permission set; the selection
            // adds to it (administrators may not remove defaults).
            $selected = array_values(array_unique(array_merge($lock, $selected)));
        }

        $ids = collect($selected)->map(fn (string $name): string => (string) Permission::updateOrCreate(['name' => $name], [
            'label' => $name,
            'module' => str($name)->before('.')->toString(),
        ])->id);

        $role->permissions()->sync($ids->all());
        ActivityLogger::log('updated permissions for role '.$role->name, 'users', $role->id);

        return redirect()->route('roles.index')
            ->with('status', 'Permissions for "'.$role->label.'" updated successfully.');
    }
}

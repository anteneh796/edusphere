<?php

namespace Database\Seeders;

use App\Domains\Accounts\Models\Permission;
use App\Domains\Accounts\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Role::where('name', 'finance_officer')->delete();
        Permission::where('name', 'like', 'finance.%')->delete();

        $matrix = config('rbac.roles');
        $labels = config('rbac.role_labels');

        $roles = [];

        foreach (array_keys($matrix) as $name) {
            $roles[$name] = Role::updateOrCreate(
                ['name' => $name],
                ['label' => $labels[$name] ?? ucfirst($name), 'description' => $labels[$name].' access']
            );
        }

        $permissions = [];

        foreach ($matrix as $roleName => $rolePermissions) {
            foreach ($rolePermissions as $permissionName) {
                $permissions[$permissionName] ??= Permission::updateOrCreate(
                    ['name' => $permissionName],
                    ['label' => $permissionName, 'module' => str($permissionName)->before('.')->toString()]
                );
            }
        }

        foreach ($matrix as $roleName => $rolePermissions) {
            $role = $roles[$roleName];
            $ids = collect($rolePermissions)->map(fn (string $name): string => (string) $permissions[$name]->getKey())->all();
            $role->permissions()->sync($ids);
        }
    }
}

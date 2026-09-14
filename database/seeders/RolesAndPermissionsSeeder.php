<?php

namespace Database\Seeders;

use App\Domains\Accounts\Models\Permission;
use App\Domains\Accounts\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Module => permissions granted per role.
     *
     * @var array<string, array<string, array<int, string>>>
     */
    private array $matrix = [
        'dashboard' => [
            'super_admin' => ['view'],
            'principal' => ['view'],
            'registrar' => ['view'],
            'teacher' => ['view'],
            'accountant' => ['view'],
            'parent' => ['view'],
            'student' => ['view'],
        ],
        'students' => [
            'super_admin' => ['view', 'create', 'edit', 'delete', 'export'],
            'principal' => ['view', 'create', 'edit', 'export'],
            'registrar' => ['view', 'create', 'edit', 'export'],
            'teacher' => ['view'],
            'accountant' => ['view'],
            'parent' => ['view'],
            'student' => ['view'],
        ],
        'guardians' => [
            'super_admin' => ['view', 'create', 'edit', 'delete', 'export'],
            'principal' => ['view', 'create', 'edit'],
            'registrar' => ['view', 'create', 'edit', 'export'],
            'teacher' => ['view'],
        ],
        'academics' => [
            'super_admin' => ['view', 'create', 'edit', 'delete', 'export'],
            'principal' => ['view', 'create', 'edit', 'delete', 'export'],
            'registrar' => ['view', 'create', 'edit'],
            'teacher' => ['view'],
        ],
        'attendance' => [
            'super_admin' => ['view', 'create', 'edit', 'export'],
            'principal' => ['view', 'create', 'export'],
            'registrar' => ['view', 'create', 'edit'],
            'teacher' => ['view', 'create', 'edit'],
        ],
        'exams' => [
            'super_admin' => ['view', 'create', 'edit', 'delete', 'export', 'publish'],
            'principal' => ['view', 'create', 'edit', 'delete', 'export', 'publish'],
            'registrar' => ['view', 'edit'],
            'teacher' => ['view', 'create', 'edit'],
            'parent' => ['view'],
            'student' => ['view'],
        ],
        'finance' => [
            'super_admin' => ['view', 'create', 'edit', 'delete', 'export'],
            'principal' => ['view', 'export'],
            'accountant' => ['view', 'create', 'edit', 'export'],
            'parent' => ['view'],
        ],
        'cms' => [
            'super_admin' => ['view', 'create', 'edit', 'delete', 'publish'],
            'principal' => ['view', 'publish'],
        ],
        'reports' => [
            'super_admin' => ['view', 'export'],
            'principal' => ['view', 'export'],
            'registrar' => ['view', 'export'],
            'teacher' => ['view'],
            'accountant' => ['view', 'export'],
            'parent' => ['view'],
            'student' => ['view'],
        ],
        'users' => [
            'super_admin' => ['view', 'create', 'edit', 'delete', 'export'],
            'principal' => ['view', 'create', 'edit'],
        ],
        'settings' => [
            'super_admin' => ['view', 'edit'],
            'principal' => ['view'],
        ],
        'audit' => [
            'super_admin' => ['view', 'export'],
            'principal' => ['view'],
        ],
    ];

    public function run(): void
    {
        $roleLabels = [
            'super_admin' => 'Super Admin',
            'principal' => 'Principal',
            'registrar' => 'Registrar',
            'teacher' => 'Teacher',
            'accountant' => 'Accountant',
            'parent' => 'Parent',
            'student' => 'Student',
        ];

        $roles = [];
        foreach ($roleLabels as $name => $label) {
            $roles[$name] = Role::updateOrCreate(
                ['name' => $name],
                ['label' => $label, 'description' => $label.' access']
            );
        }

        $permissions = [];
        foreach ($this->matrix as $module => $rolePerms) {
            $actions = collect($rolePerms)->flatten()->unique();
            foreach ($actions as $action) {
                $name = $module.'.'.$action;
                $permissions[$name] ??= Permission::updateOrCreate(
                    ['name' => $name],
                    ['label' => Str::title($action).' '.ucfirst($module), 'module' => $module]
                );
            }
        }

        foreach ($this->matrix as $module => $rolePerms) {
            foreach ($rolePerms as $roleName => $actions) {
                $perms = collect($actions)->map(fn ($a) => $permissions[$module.'.'.$a]->getKey());
                $roles[$roleName]->permissions()->syncWithoutDetaching($perms->all());
            }
        }
    }
}

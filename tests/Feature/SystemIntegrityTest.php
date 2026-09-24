<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SystemIntegrityTest extends TestCase
{
    public function test_registered_controller_routes_point_to_existing_classes_and_methods(): void
    {
        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();

            if ($action === 'Closure' || ! str_contains($action, '@')) {
                continue;
            }

            [$class, $method] = explode('@', $action, 2);

            $this->assertTrue(
                class_exists($class),
                "Route [{$route->uri()}] references missing controller [{$class}]."
            );

            $this->assertTrue(
                method_exists($class, $method),
                "Route [{$route->uri()}] references missing method [{$class}@{$method}]."
            );
        }
    }

    public function test_route_security_references_exist_in_rbac_configuration(): void
    {
        $routeSource = file_get_contents(base_path('routes/web.php'));

        preg_match_all('/permission:([a-z0-9_.-]+)/', $routeSource, $permissionMatches);
        preg_match_all('/role:([a-z0-9_-]+)/', $routeSource, $roleMatches);

        $declaredPermissions = collect(config('rbac.roles', []))
            ->flatten()
            ->unique()
            ->values();

        foreach (array_unique($permissionMatches[1] ?? []) as $permission) {
            $this->assertTrue(
                $declaredPermissions->contains($permission),
                "Route references undeclared permission [{$permission}]."
            );
        }

        $declaredRoles = array_keys(config('rbac.role_labels', []));

        foreach (array_unique($roleMatches[1] ?? []) as $role) {
            $this->assertContains(
                $role,
                $declaredRoles,
                "Route references undeclared role [{$role}]."
            );
        }
    }

    public function test_rbac_configuration_contains_only_declared_roles_and_permissions(): void
    {
        $roles = config('rbac.roles', []);
        $labels = config('rbac.role_labels', []);

        $this->assertNotEmpty($roles);
        $this->assertSame(
            array_keys($roles),
            array_keys($labels),
            'Every configured role must have a role label.'
        );

        $permissions = collect($roles)
            ->flatten()
            ->filter()
            ->values();

        foreach ($roles as $roleName => $rolePermissions) {
            $rolePermissions = collect($rolePermissions)->filter()->values();

            $this->assertSame(
                $rolePermissions->count(),
                $rolePermissions->unique()->count(),
                "Role [{$roleName}] should not contain duplicate permissions."
            );
        }

        $this->assertFalse(
            $permissions->contains(fn (string $permission) => str_starts_with($permission, 'finance.')),
            'Removed Finance permissions must not return to RBAC.'
        );
    }
}

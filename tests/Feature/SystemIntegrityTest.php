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

        $this->assertSame(
            $permissions->count(),
            $permissions->unique()->count(),
            'A role should not contain duplicate permissions.'
        );

        $this->assertFalse(
            $permissions->contains(fn (string $permission) => str_starts_with($permission, 'finance.')),
            'Removed Finance permissions must not return to RBAC.'
        );
    }
}

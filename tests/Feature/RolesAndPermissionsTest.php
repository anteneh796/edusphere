<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function role(string $name): Role
    {
        return Role::create(['name' => $name, 'label' => $name]);
    }

    private function user(string $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role($roleName));

        return $user;
    }

    public function test_roles_are_auto_granted_their_configured_permissions(): void
    {
        $teacher = $this->role(RoleName::Teacher->value);

        $this->assertTrue($teacher->permissions->pluck('name')->contains('students.view'));
        $this->assertTrue($teacher->permissions()->where('name', 'exams.view')->exists());
        $this->assertFalse($teacher->permissions->pluck('name')->contains('users.view'));
    }

    public function test_permission_middleware_blocks_actions_outside_the_matrix(): void
    {
        $registrar = $this->user(RoleName::Registrar->value);
        $teacher = $this->user(RoleName::Teacher->value);

        $this->actingAs($registrar)
            ->get(route('settings.index'))
            ->assertForbidden();

        $this->actingAs($registrar)
            ->post(route('academics.subjects.store'), ['name' => 'Physics', 'code' => 'PHY'])
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('cms.index'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('exams.create'))
            ->assertForbidden();
    }

    public function test_permission_middleware_allows_authorized_access(): void
    {
        AcademicYear::factory()->current()->create();

        $registrar = $this->user(RoleName::Registrar->value);
        $teacher = $this->user(RoleName::Teacher->value);

        $this->actingAs($registrar)
            ->get(route('academics.index'))
            ->assertOk();

        $this->actingAs($teacher)
            ->get(route('students.index'))
            ->assertOk();

        $this->actingAs($teacher)
            ->get(route('exams.index'))
            ->assertOk();
    }

    public function test_super_admin_can_review_session_security_pages(): void
    {
        $admin = $this->user(RoleName::SuperAdmin->value);

        $this->actingAs($admin)
            ->get(route('security.sessions'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('security.login-history'))
            ->assertOk();
    }

    public function test_permanent_role_edits_keep_locked_defaults_and_allow_additions(): void
    {
        $admin = $this->user(RoleName::SuperAdmin->value);
        $principal = $this->role(RoleName::Principal->value);

        $defaults = config('rbac.roles.principal');

        $this->assertNotEmpty($defaults);

        $this->actingAs($admin)
            ->put(route('roles.update', $principal), [
                'permissions' => ['cms.view', 'users.view'],
            ])
            ->assertRedirect(route('roles.index'));

        $principal->refresh();

        foreach ($defaults as $permission) {
            $this->assertTrue($principal->permissions()->where('name', $permission)->exists());
        }

        $this->assertTrue($principal->permissions()->where('name', 'users.view')->exists());
    }
}

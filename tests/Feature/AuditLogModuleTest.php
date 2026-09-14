<?php

namespace Tests\Feature;

use App\Domains\Accounts\Models\AuditLog;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            RoleName::SuperAdmin->value => 'Super Admin',
            RoleName::Principal->value => 'Principal',
            RoleName::Teacher->value => 'Teacher',
            RoleName::Registrar->value => 'Registrar',
        ] as $name => $label) {
            Role::firstOrCreate(['name' => $name], ['label' => $label]);
        }
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $role)->first());

        return $user;
    }

    public function test_principal_can_view_the_audit_log(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);
        AuditLog::factory()->create(['action' => 'visits']);

        $this->actingAs($principal)
            ->get(route('audit.index'))
            ->assertOk()
            ->assertSee('visits');
    }

    public function test_teacher_cannot_view_the_audit_log(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);

        $this->actingAs($teacher)
            ->get(route('audit.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_inspect_a_single_entry(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);
        $log = AuditLog::factory()->create(['module' => 'users', 'meta' => ['key' => 'value']]);

        $this->actingAs($admin)
            ->get(route('audit.show', $log))
            ->assertOk()
            ->assertSee('users')
            ->assertSee('"key"');
    }
}

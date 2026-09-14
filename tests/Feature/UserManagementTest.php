<?php

namespace Tests\Feature;

use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function superAdmin(): User
    {
        Role::create(['name' => RoleName::SuperAdmin->value, 'label' => 'Super Admin']);
        Role::create(['name' => RoleName::Teacher->value, 'label' => 'Teacher']);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', RoleName::SuperAdmin->value)->first());

        return $admin;
    }

    public function test_super_admin_can_create_a_user(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane@edu.et',
                'phone' => '+251 911 000 000',
                'password' => 'Password@123',
                'password_confirmation' => 'Password@123',
                'status' => 'active',
                'roles' => [Role::where('name', RoleName::Teacher->value)->first()->getKey()],
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', ['email' => 'jane@edu.et']);
        $this->assertDatabaseHas('role_user', [
            'user_id' => User::where('email', 'jane@edu.et')->first()->getKey(),
            'role_id' => Role::where('name', RoleName::Teacher->value)->first()->getKey(),
        ]);
    }

    public function test_user_validation_rejects_duplicate_emails(): void
    {
        $admin = $this->superAdmin();
        User::factory()->create(['email' => 'taken@edu.et']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'taken@edu.et',
                'password' => 'Password@123',
                'password_confirmation' => 'Password@123',
                'status' => 'active',
                'roles' => [Role::where('name', RoleName::Teacher->value)->first()->getKey()],
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_principal_cannot_delete_users(): void
    {
        $admin = $this->superAdmin();
        $principalRole = Role::create(['name' => RoleName::Principal->value, 'label' => 'Principal']);

        $principal = User::factory()->create();
        $principal->roles()->attach($principalRole->getKey());

        $target = User::factory()->create();

        $this->actingAs($principal)
            ->delete(route('users.destroy', $target))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $target->getKey()]);
    }

    public function test_super_admin_can_soft_delete_a_user(): void
    {
        $admin = $this->superAdmin();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('users.destroy', $target))
            ->assertRedirect(route('users.index'));

        $this->assertSoftDeleted('users', ['id' => $target->getKey()]);
    }
}

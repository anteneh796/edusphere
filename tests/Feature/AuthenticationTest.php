<?php

namespace Tests\Feature;

use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Support\Enums\RoleName;
use Database\Seeders\AcademicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function seedUsers(): User
    {
        Role::create(['name' => RoleName::SuperAdmin->value, 'label' => 'Super Admin']);
        Role::create(['name' => RoleName::Teacher->value, 'label' => 'Teacher']);
        Role::create(['name' => RoleName::Principal->value, 'label' => 'Principal']);
        Role::create(['name' => RoleName::Student->value, 'label' => 'Student']);
        Role::create(['name' => RoleName::Parent->value, 'label' => 'Parent']);

        $admin = User::factory()->create([
            'email' => 'admin@edusphere.com',
            'password' => 'Admin@2026',
        ]);
        $admin->roles()->attach(Role::where('name', RoleName::SuperAdmin->value)->first());

        $this->seed(AcademicSeeder::class);

        return $admin;
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect(route('auth.login'));
    }

    public function test_login_page_renders(): void
    {
        $this->get(route('auth.login'))->assertOk()->assertSee('Sign in');
    }

    public function test_valid_credentials_redirect_to_dashboard(): void
    {
        $this->seedUsers();

        $this->post(route('auth.authenticate'), [
            'login' => 'admin@edusphere.com',
            'password' => 'Admin@2026',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_valid_credentials_with_remember_me_redirect_to_dashboard(): void
    {
        $this->seedUsers();

        $this->post(route('auth.authenticate'), [
            'login' => 'admin@edusphere.com',
            'password' => 'Admin@2026',
            'remember' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_student_login_redirects_to_student_portal(): void
    {
        $this->seedUsers();

        $student = User::factory()->create([
            'email' => 'student@edusphere.com',
            'password' => 'Dev@2026',
        ]);
        $student->roles()->attach(Role::where('name', RoleName::Student->value)->first());

        $this->post(route('auth.authenticate'), [
            'login' => 'student@edusphere.com',
            'password' => 'Dev@2026',
        ])->assertRedirect(route('cms.student.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_parent_login_redirects_to_parent_portal(): void
    {
        $this->seedUsers();

        $parent = User::factory()->create([
            'email' => 'parent@edusphere.com',
            'password' => 'Dev@2026',
        ]);
        $parent->roles()->attach(Role::where('name', RoleName::Parent->value)->first());

        $this->post(route('auth.authenticate'), [
            'login' => 'parent@edusphere.com',
            'password' => 'Dev@2026',
        ])->assertRedirect(route('cms.parent.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_student_hitting_dashboard_redirects_to_student_portal(): void
    {
        $this->seedUsers();

        $student = User::factory()->create();
        $student->roles()->attach(Role::where('name', RoleName::Student->value)->first());

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertRedirect(route('cms.student.dashboard'));
    }

    public function test_student_portal_pages_render(): void
    {
        $this->seedUsers();

        $student = User::factory()->create();
        $student->roles()->attach(Role::where('name', RoleName::Student->value)->first());

        $this->actingAs($student)
            ->get(route('cms.student.dashboard'))
            ->assertOk();

        $this->actingAs($student)
            ->get(route('cms.student.attendance'))
            ->assertOk();

        $this->actingAs($student)
            ->get(route('cms.student.results'))
            ->assertOk();

        $this->actingAs($student)
            ->get(route('cms.student.profile'))
            ->assertOk();
    }

    public function test_parent_portal_pages_render(): void
    {
        $this->seedUsers();

        $parent = User::factory()->create();
        $parent->roles()->attach(Role::where('name', RoleName::Parent->value)->first());

        $this->actingAs($parent)
            ->get(route('cms.parent.dashboard'))
            ->assertOk();

        $this->actingAs($parent)
            ->get(route('cms.parent.billing'))
            ->assertOk();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->seedUsers();

        $this->post(route('auth.authenticate'), [
            'login' => 'admin@edusphere.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_suspended_account_cannot_sign_in(): void
    {
        $this->seedUsers();

        $suspended = User::factory()->create(['status' => 'suspended']);
        $suspended->roles()->attach(Role::where('name', RoleName::Teacher->value)->first());

        $this->post(route('auth.authenticate'), [
            'login' => $suspended->email,
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_logout_terminates_the_session(): void
    {
        $this->seedUsers();

        $this->actingAs(User::first())
            ->post(route('auth.logout'));

        $this->assertGuest();
    }

    public function test_role_middleware_blocks_forbidden_roles(): void
    {
        $this->seedUsers();

        $teacher = User::factory()->create();
        $teacher->roles()->attach(Role::where('name', RoleName::Teacher->value)->first());

        $this->actingAs($teacher)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('students.index'))
            ->assertOk();
    }
}

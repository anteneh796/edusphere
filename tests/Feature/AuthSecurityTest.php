<?php

namespace Tests\Feature;

use App\Domains\Accounts\Models\LoginLog;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Accounts\Services\PasswordService;
use App\Domains\Settings\Models\Setting;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function role(string $name): Role
    {
        return Role::create(['name' => $name, 'label' => $name]);
    }

    private function user(string $roleName, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->roles()->attach($this->role($roleName));

        return $user;
    }

    public function test_users_can_sign_in_with_email_username_employee_id_or_student_number(): void
    {
        $teacher = $this->user(RoleName::Teacher->value, [
            'username' => 'sara.haile',
            'employee_id' => 'EMP-0042',
        ]);
        $student = $this->user(RoleName::Student->value, ['student_number' => 'ES-26-0101']);

        $this->post(route('auth.authenticate'), [
            'login' => $teacher->username,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($teacher);

        $this->post(route('auth.logout'));

        $this->post(route('auth.authenticate'), [
            'login' => $teacher->employee_id,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($teacher);

        $this->post(route('auth.logout'));

        $this->post(route('auth.authenticate'), [
            'login' => $student->student_number,
            'password' => 'password',
        ])->assertRedirect(route('cms.student.dashboard'));

        $this->assertAuthenticatedAs($student);
    }

    public function test_disabled_identifier_methods_block_those_logins(): void
    {
        $teacher = $this->user(RoleName::Teacher->value, ['username' => 'sara.haile']);

        Setting::set('login_method_username', '0');

        $this->post(route('auth.authenticate'), [
            'login' => $teacher->username,
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();

        $this->assertDatabaseHas('login_logs', [
            'user_id' => null,
            'event' => 'failed',
            'identifier' => $teacher->username,
        ]);
    }

    public function test_successful_login_records_login_log_and_last_login_metadata(): void
    {
        $teacher = $this->user(RoleName::Teacher->value, ['username' => 'sara.haile']);

        $this->post(route('auth.authenticate'), [
            'login' => $teacher->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($teacher);

        $log = LoginLog::where('user_id', $teacher->getKey())->where('event', 'login')->latest()->first();

        $this->assertNotNull($log);
        $this->assertSame($teacher->email, $log->identifier);
        $this->assertNotEmpty($log->session_id);
        $this->assertNotNull($log->device);
        $this->assertNotNull($log->browser);
        $this->assertNotNull($log->login_at);
        $this->assertSame($log->getKey(), session()->get('login_log_id'));
        $this->assertNotNull($teacher->fresh()->last_login_at);
    }

    public function test_failed_login_records_a_failed_log_and_stays_guest(): void
    {
        $teacher = $this->user(RoleName::Teacher->value, ['username' => 'sara.haile']);

        $this->post(route('auth.authenticate'), [
            'login' => $teacher->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();

        $this->assertDatabaseHas('login_logs', [
            'user_id' => $teacher->getKey(),
            'event' => 'failed',
            'identifier' => $teacher->email,
        ]);
    }

    public function test_logout_marks_login_log_and_must_change_redirects_new_logins(): void
    {
        $teacher = $this->user(RoleName::Teacher->value);

        $this->post(route('auth.authenticate'), [
            'login' => $teacher->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->post(route('auth.logout'));
        $this->assertGuest();

        $log = LoginLog::where('user_id', $teacher->getKey())->where('event', 'login')->latest()->first();
        $this->assertNotNull($log->logout_at);

        $teacher->forceFill(['must_change_password' => true])->save();

        $this->post(route('auth.authenticate'), [
            'login' => $teacher->email,
            'password' => 'password',
        ])->assertRedirect(route('profile.security'));

        $this->actingAs($teacher->fresh())
            ->get(route('dashboard'))
            ->assertRedirect(route('profile.security'));

        $this->actingAs($teacher->fresh())
            ->get(route('profile.security'))
            ->assertOk();
    }

    public function test_admin_password_reset_forces_change_and_rejects_the_old_password(): void
    {
        $admin = $this->user(RoleName::SuperAdmin->value);
        $target = $this->user(RoleName::Teacher->value, ['password' => 'Original@1']);

        $this->actingAs($admin)
            ->post(route('users.reset-password', $target), [
                'password' => 'NewPass@2026',
                'password_confirmation' => 'NewPass@2026',
            ])
            ->assertRedirect(route('users.show', $target));

        $target->refresh();

        $this->assertTrue($target->must_change_password);
        $this->assertTrue(PasswordService::wasUsed($target, 'Original@1'));

        $this->post(route('auth.logout'));

        $this->post(route('auth.authenticate'), [
            'login' => $target->email,
            'password' => 'Original@1',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();

        $this->post(route('auth.authenticate'), [
            'login' => $target->email,
            'password' => 'NewPass@2026',
        ])->assertRedirect(route('profile.security'));
    }

    public function test_profile_password_change_rejects_reusing_the_current_password(): void
    {
        $teacher = $this->user(RoleName::Teacher->value, ['password' => 'Current@1']);

        $this->actingAs($teacher)
            ->put(route('profile.password'), [
                'current_password' => 'Current@1',
                'new_password' => 'Current@1',
                'new_password_confirmation' => 'Current@1',
            ])
            ->assertSessionHasErrors('new_password');
    }

    public function test_idle_session_is_expired_and_logged(): void
    {
        Setting::set('session_timeout_teacher', '1');

        $teacher = $this->user(RoleName::Teacher->value);

        $this->post(route('auth.authenticate'), [
            'login' => $teacher->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        session()->put('last_activity_at', now()->subMinutes(2)->timestamp);

        $this->get(route('dashboard'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHasErrors('login');

        $this->assertGuest();

        $this->assertDatabaseHas('login_logs', [
            'user_id' => $teacher->getKey(),
            'event' => 'expired',
        ]);
    }
}

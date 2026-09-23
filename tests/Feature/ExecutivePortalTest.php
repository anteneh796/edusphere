<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicTerm;
use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Section;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Approvals\Models\ApprovalRequest;
use App\Domains\Approvals\Services\ApprovalService;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Students\Models\Student;
use App\Support\Enums\ApprovalStatus;
use App\Support\Enums\ApprovalType;
use App\Support\Enums\RoleName;
use App\Support\Enums\StudentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutivePortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (RoleName::cases() as $roleName) {
            Role::create(['name' => $roleName->value, 'label' => $roleName->label()]);
        }

        AcademicYear::factory()->current()->create();
    }

    protected function userWithRole(RoleName $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $roleName->value)->firstOrFail());

        return $user;
    }

    protected function enrolledStudent(string $status = StudentStatus::Active->value): Student
    {
        $year = AcademicYear::current()->firstOrFail();
        $grade = GradeLevel::create(['name' => 'Grade 3', 'code' => '3', 'stage' => 'lower_primary', 'sort_order' => 4]);
        $class = ClassRoom::create([
            'grade_level_id' => $grade->getKey(),
            'academic_year_id' => $year->getKey(),
            'name' => '3 A',
            'capacity' => 40,
        ]);

        return Student::factory()->create([
            'grade_level_id' => $grade->getKey(),
            'class_room_id' => $class->getKey(),
            'academic_year_id' => $year->getKey(),
            'status' => $status,
        ]);
    }

    public function test_grade_codes_outside_the_kg_through_eight_offering_are_rejected(): void
    {
        $principal = $this->userWithRole(RoleName::Principal);

        $this->actingAs($principal)
            ->post(route('academics.grades.store'), [
                'name' => 'Grade 9',
                'code' => '9',
                'stage' => 'upper_primary',
            ])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseMissing('grade_levels', ['code' => '9']);

        $this->actingAs($principal)
            ->post(route('academics.grades.store'), [
                'name' => 'Kindergarten 1',
                'code' => 'KG1',
                'stage' => 'kindergarten',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('grade_levels', ['code' => 'KG1']);
    }

    public function test_section_names_are_unique_per_year_but_reusable_across_years(): void
    {
        $principal = $this->userWithRole(RoleName::Principal);
        $year = AcademicYear::current()->firstOrFail();
        $otherYear = AcademicYear::create([
            'name' => '2024/2025',
            'start_date' => '2024-09-10',
            'end_date' => '2025-07-02',
            'is_current' => false,
        ]);

        $this->actingAs($principal)
            ->post(route('academics.sections.store'), [
                'academic_year_id' => $year->getKey(),
                'name' => 'Morning',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($principal)
            ->post(route('academics.sections.store'), [
                'academic_year_id' => $year->getKey(),
                'name' => 'Morning',
            ])
            ->assertSessionHasErrors('name');

        $this->actingAs($principal)
            ->post(route('academics.sections.store'), [
                'academic_year_id' => $otherYear->getKey(),
                'name' => 'Morning',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Section::where('name', 'Morning')->count());
    }

    public function test_current_term_is_protected_from_delete_and_activation_switches_the_flag(): void
    {
        $principal = $this->userWithRole(RoleName::Principal);
        $year = AcademicYear::current()->firstOrFail();

        $currentTerm = AcademicTerm::factory()->current()->create(['academic_year_id' => $year->getKey()]);
        $nextTerm = AcademicTerm::create([
            'academic_year_id' => $year->getKey(),
            'name' => 'Term 2',
            'sequence' => 2,
            'start_date' => now()->addMonths(4),
            'end_date' => now()->addMonths(7),
            'is_current' => false,
        ]);

        $this->actingAs($principal)
            ->post(route('academics.terms.activate', $nextTerm))
            ->assertRedirect(route('academics.terms.index'));

        $this->assertFalse($currentTerm->fresh()->is_current);
        $this->assertTrue($nextTerm->fresh()->is_current);

        $this->from(route('academics.terms.index'))
            ->actingAs($principal)
            ->delete(route('academics.terms.destroy', $nextTerm))
            ->assertRedirect(route('academics.terms.index'))
            ->assertSessionHasErrors('term');

        $this->assertModelExists($nextTerm->fresh());
    }

    public function test_current_academic_year_is_protected_from_delete(): void
    {
        $principal = $this->userWithRole(RoleName::Principal);
        $year = AcademicYear::current()->firstOrFail();

        $this->from(route('academics.years.index'))
            ->actingAs($principal)
            ->delete(route('academics.years.destroy', $year))
            ->assertRedirect(route('academics.years.index'))
            ->assertSessionHasErrors('year');

        $this->assertModelExists($year->fresh());
    }

    public function test_staff_member_can_be_created_but_cannot_carry_student_roles(): void
    {
        $principal = $this->userWithRole(RoleName::Principal);
        $teacherRole = Role::where('name', RoleName::Teacher->value)->firstOrFail();
        $studentRole = Role::where('name', RoleName::Student->value)->firstOrFail();
        $payload = [
            'first_name' => 'Biruk',
            'last_name' => 'Abebe',
            'email' => 'biruk.abebe@school.et',
            'employee_id' => 'EMP-001',
            'staff_type' => 'teaching',
            'department' => 'Science',
            'job_title' => 'General Science Teacher',
            'hire_date' => '2024-09-01',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
            'status' => 'active',
            'roles' => [$teacherRole->id],
        ];

        $this->actingAs($principal)
            ->post(route('staff.store'), $payload)
            ->assertSessionHasNoErrors();

        $staff = User::where('employee_id', 'EMP-001')->firstOrFail();
        $this->assertSame('Biruk Abebe', $staff->full_name);

        $payload['email'] = 'someone.else@school.et';
        $payload['roles'] = [$studentRole->id];

        $this->actingAs($principal)
            ->post(route('staff.store'), $payload)
            ->assertSessionHasErrors('roles.0');

        $this->assertDatabaseMissing('users', ['email' => $payload['email']]);
    }

    public function test_employee_id_is_unique_across_staff_members(): void
    {
        $principal = $this->userWithRole(RoleName::Principal);
        $teacherRole = Role::where('name', RoleName::Teacher->value)->firstOrFail();
        $base = [
            'staff_type' => 'teaching',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
            'status' => 'active',
            'roles' => [$teacherRole->id],
        ];

        $this->actingAs($principal)
            ->post(route('staff.store'), [
                ...$base,
                'first_name' => 'Alem',
                'last_name' => 'Tesfaye',
                'email' => 'alem.tesfaye@school.et',
                'employee_id' => 'EMP-002',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($principal)
            ->post(route('staff.store'), [
                ...$base,
                'first_name' => 'Sara',
                'last_name' => 'Negash',
                'email' => 'sara.negash@school.et',
                'employee_id' => 'EMP-002',
            ])
            ->assertSessionHasErrors('employee_id');
    }

    public function test_approval_submission_notifies_reviewers_and_principal_approval_applies_the_transfer(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar);
        $principal = $this->userWithRole(RoleName::Principal);
        $student = $this->enrolledStudent();

        $this->actingAs($registrar)
            ->post(route('approvals.store'), [
                'type' => ApprovalType::StudentTransfer->value,
                'reason' => 'Family relocation to Adama.',
                'student_id' => $student->getKey(),
            ])
            ->assertSessionHasNoErrors();

        $request = ApprovalRequest::where('type', ApprovalType::StudentTransfer->value)->firstOrFail();
        $this->assertTrue($request->isPending());

        $this->assertSame(1, Notification::where('user_id', $principal->getKey())->unread()->count());

        $this->actingAs($principal)
            ->post(route('approvals.review', $request), [
                'action' => 'approve',
                'reviewer_note' => 'Duly processed.',
            ])
            ->assertRedirect(route('approvals.show', $request));

        $this->assertSame(ApprovalStatus::Approved->value, $request->fresh()->status);
        $this->assertSame(StudentStatus::Transferred->value, $student->fresh()->status);
        $this->assertSame(1, Notification::where('user_id', $registrar->getKey())->unread()->count());
    }

    public function test_registrar_cannot_review_a_request_created_by_another_user(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar);
        $teacher = $this->userWithRole(RoleName::Teacher);
        $student = $this->enrolledStudent();

        $request = app(ApprovalService::class)->submit(
            ApprovalType::StudentTransfer,
            $teacher,
            'Teacher-initiated transfer.',
            Student::class,
            $student->getKey(),
        );

        $this->actingAs($registrar)
            ->post(route('approvals.review', $request), ['action' => 'deny'])
            ->assertForbidden();

        $this->assertTrue($request->fresh()->isPending());
    }

    public function test_notifications_are_isolated_per_user_and_open_marks_read(): void
    {
        $principal = $this->userWithRole(RoleName::Principal);
        $registrar = $this->userWithRole(RoleName::Registrar);

        Notification::factory()->create(['user_id' => $registrar->getKey(), 'title' => 'PRIVATE-REG-NOTE']);
        $own = Notification::factory()->unread()->create(['user_id' => $principal->getKey()]);

        $this->actingAs($principal)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertDontSee('PRIVATE-REG-NOTE');

        $this->actingAs($registrar)
            ->get(route('notifications.show', $own))
            ->assertForbidden();

        $this->actingAs($principal)
            ->get(route('notifications.show', $own))
            ->assertRedirect(route('notifications.index'));

        $this->assertNotNull($own->fresh()->read_at);
    }

    public function test_reports_and_dashboard_render_for_a_principal(): void
    {
        $principal = $this->userWithRole(RoleName::Principal);

        $this->actingAs($principal)
            ->get(route('reports.index'))
            ->assertOk();

        $this->actingAs($principal)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Approvals queue');
    }

    public function test_executive_index_pages_render_for_a_principal(): void
    {
        $principal = $this->userWithRole(RoleName::Principal);

        foreach ([
            'academics.index',
            'academics.grades.index',
            'academics.classes.index',
            'academics.subjects.index',
            'academics.years.index',
            'academics.sections.index',
            'academics.terms.index',
            'staff.index',
            'approvals.index',
            'notifications.index',
        ] as $route) {
            $this->actingAs($principal)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_settings_localization_values_round_trip(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin);

        $this->actingAs($admin)
            ->put(route('settings.update'), [
                'school_name' => 'Addis Academy',
                'school_tagline' => 'Tadias!',
                'school_email' => 'info@addisacademy.et',
                'school_phone' => '+251 1 123 4567',
                'school_address' => 'Bole, Addis Ababa',
                'school_website' => 'https://addisacademy.et',
                'currency' => 'ETB',
                'timezone' => 'America/New_York',
                'language' => 'am',
                'academic_year' => '2026/2027',
                'announcement_enabled' => '0',
                'announcement_text' => '',
                'social_facebook' => '',
                'social_instagram' => '',
                'social_telegram' => '',
                'login_method_email' => '1',
                'login_method_username' => '0',
                'login_method_employee_id' => '0',
                'login_method_student_id' => '0',
                'password_min_length' => 8,
                'password_require_uppercase' => '1',
                'password_require_lowercase' => '1',
                'password_require_number' => '1',
                'password_require_symbol' => '0',
                'password_history_count' => 5,
                'password_expiration_days' => 0,
                'session_timeout_global' => 30,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'timezone', 'value' => 'America/New_York']);
        $this->assertDatabaseHas('settings', ['key' => 'language', 'value' => 'am']);
    }
}

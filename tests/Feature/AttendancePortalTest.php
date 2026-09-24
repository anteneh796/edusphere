<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceCorrection;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Services\AttendanceService;
use App\Domains\Settings\Models\Setting;
use App\Domains\Students\Models\Student;
use App\Support\Enums\AttendanceCorrectionStatus;
use App\Support\Enums\AttendanceSessionStatus;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendancePortalTest extends TestCase
{
    use RefreshDatabase;

    private string $classId;

    private Student $firstStudent;

    private Student $secondStudent;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => RoleName::SuperAdmin->value, 'label' => 'Super Admin']);
        Role::create(['name' => RoleName::SchoolAdmin->value, 'label' => 'School Admin']);
        Role::create(['name' => RoleName::Principal->value, 'label' => 'Principal']);
        Role::create(['name' => RoleName::Registrar->value, 'label' => 'Registrar']);
        Role::create(['name' => RoleName::Teacher->value, 'label' => 'Teacher']);

        $year = AcademicYear::factory()->current()->create();
        $grade = GradeLevel::create(['name' => 'Grade 5', 'code' => '5', 'sort_order' => 6]);
        $class = ClassRoom::create([
            'grade_level_id' => $grade->getKey(),
            'academic_year_id' => $year->getKey(),
            'name' => '5 A',
            'capacity' => 40,
        ]);

        $this->classId = $class->getKey();
        $this->firstStudent = $this->enrollStudent($class, 'ES-26-0001');
        $this->secondStudent = $this->enrollStudent($class, 'ES-26-0002');
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $roleName)->firstOrFail());

        return $user;
    }

    private function enrollStudent(ClassRoom $class, string $number): Student
    {
        $student = Student::factory()->create([
            'student_number' => $number,
            'grade_level_id' => $class->grade_level_id,
            'class_room_id' => $class->getKey(),
            'academic_year_id' => $class->academic_year_id,
        ]);

        $student->enrollments()->create([
            'academic_year_id' => $class->academic_year_id,
            'grade_level_id' => $class->grade_level_id,
            'class_room_id' => $class->getKey(),
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        return $student;
    }

    private function openSession(User $user, ?Carbon $date = null): AttendanceSession
    {
        return AttendanceSession::create([
            'class_room_id' => $this->classId,
            'academic_year_id' => AcademicYear::current()->firstOrFail()->getKey(),
            'taken_by_id' => $user->getKey(),
            'date' => ($date ?? Carbon::today())->toDateString(),
            'status' => AttendanceSessionStatus::Open->value,
            'opened_at' => $date ?? now(),
        ]);
    }

    private function markLockedSession(User $user, string $firstStatus = 'absent', string $secondStatus = 'present', ?Carbon $date = null): AttendanceSession
    {
        $session = $this->openSession($user, $date);

        (new AttendanceService)->upsertRecords($session, $user->getKey(), [
            ['student_id' => $this->firstStudent->getKey(), 'status' => $firstStatus],
            ['student_id' => $this->secondStudent->getKey(), 'status' => $secondStatus],
        ]);

        (new AttendanceService)->closeSession($session);
        $session->refresh();

        return $session;
    }

    private function assignTeacherToClass(User $teacher): void
    {
        $subject = Subject::create(['name' => 'Mathematics', 'code' => 'MAT']);

        ClassSubject::create([
            'class_room_id' => $this->classId,
            'subject_id' => $subject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'periods_per_week' => 5,
            'position' => 1,
            'is_homeroom' => true,
        ]);
    }

    /* --------------------------------- Dashboard -------------------------------- */

    public function test_super_admin_sees_today_attendance_dashboard(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);
        $session = $this->markLockedSession($admin);

        $this->actingAs($admin)
            ->get(route('attendance.dashboard'))
            ->assertOk()
            ->assertSee('Attendance overview')
            ->assertSee('Submitted &amp; locked', false)
            ->assertSee('5 A');
    }


    /* --------------------------------- Reports --------------------------------- */

    public function test_reports_pages_render_for_an_admin(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);
        $this->markLockedSession($admin);

        $routes = [
            'attendance.reports.daily',
            'attendance.reports.monthly',
            'attendance.reports.status',
            'attendance.reports.trend',
            'attendance.reports.completion',
            'attendance.reports.class',
            'attendance.reports.grade',
            'attendance.reports.late',
            'attendance.reports.term',
        ];

        foreach ($routes as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }

        $this->actingAs($admin)
            ->get(route('attendance.reports.student', ['student_id' => $this->firstStudent->getKey()]))
            ->assertOk()
            ->assertSee($this->firstStudent->full_name);
    }

    public function test_status_report_filters_by_status(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);
        $this->markLockedSession($admin);

        $this->actingAs($admin)
            ->get(route('attendance.reports.status', ['status' => 'absent']))
            ->assertOk()
            ->assertSee($this->firstStudent->full_name)
            ->assertDontSee($this->secondStudent->full_name);
    }

    /* ------------------------------ Corrections -------------------------------- */

    public function test_teacher_can_request_a_correction_on_a_locked_session(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $this->assignTeacherToClass($teacher);
        $session = $this->markLockedSession($teacher, 'absent', 'present');

        $this->actingAs($teacher)
            ->post(route('cms.teacher.attendance.correction', $session), [
                'student_id' => $this->firstStudent->getKey(),
                'requested_status' => 'present',
                'reason' => 'Arrived late, then attended.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_record_id' => $this->firstStudent->attendanceRecords()->firstOrFail()->getKey(),
            'requested_status' => 'present',
            'status' => 'pending',
        ]);
    }

    public function test_teacher_cannot_request_a_correction_on_an_open_session(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $this->assignTeacherToClass($teacher);
        $session = $this->openSession($teacher);

        $this->actingAs($teacher)
            ->post(route('cms.teacher.attendance.correction', $session), [
                'student_id' => $this->firstStudent->getKey(),
                'requested_status' => 'present',
                'reason' => 'Attended.',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('correction');

        $this->assertSame(0, AttendanceCorrection::count());
    }

    public function test_admin_approving_a_correction_applies_the_new_status(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $this->assignTeacherToClass($teacher);
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);
        $session = $this->markLockedSession($teacher, 'absent', 'present');

        $this->actingAs($teacher)->post(route('cms.teacher.attendance.correction', $session), [
            'student_id' => $this->firstStudent->getKey(),
            'requested_status' => 'late',
            'reason' => 'Bus broke down.',
        ]);

        $correction = AttendanceCorrection::firstOrFail();

        $this->actingAs($admin)
            ->post(route('attendance.corrections.review', $correction), [
                'decision' => 'approve',
                'reviewer_note' => 'Looks correct.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $correction->refresh();

        $this->assertEquals(AttendanceCorrectionStatus::Approved, $correction->status);
        $this->assertEquals('late', $this->firstStudent->attendanceRecords()->firstOrFail()->status->value);
    }

    public function test_admin_rejecting_a_correction_keeps_the_record_unchanged(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $this->assignTeacherToClass($teacher);
        $admin = $this->userWithRole(RoleName::Principal->value);
        $session = $this->markLockedSession($teacher, 'absent', 'present');

        $this->actingAs($teacher)->post(route('cms.teacher.attendance.correction', $session), [
            'student_id' => $this->firstStudent->getKey(),
            'requested_status' => 'present',
            'reason' => 'Was present.',
        ]);

        $correction = AttendanceCorrection::firstOrFail();

        $this->actingAs($admin)
            ->post(route('attendance.corrections.review', $correction), [
                'decision' => 'reject',
                'reviewer_note' => 'No evidence.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $correction->refresh();

        $this->assertEquals(AttendanceCorrectionStatus::Rejected, $correction->status);
        $this->assertEquals('absent', $this->firstStudent->attendanceRecords()->firstOrFail()->status->value);
    }

    public function test_a_teacher_cannot_review_corrections(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $this->assignTeacherToClass($teacher);
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);
        $session = $this->markLockedSession($teacher, 'absent', 'present');

        $this->actingAs($teacher)->post(route('cms.teacher.attendance.correction', $session), [
            'student_id' => $this->firstStudent->getKey(),
            'requested_status' => 'present',
            'reason' => 'Was present.',
        ]);

        $correction = AttendanceCorrection::firstOrFail();

        $this->actingAs($teacher)
            ->post(route('attendance.corrections.review', $correction), [
                'decision' => 'approve',
            ])
            ->assertForbidden();

        $correction->refresh();
        $this->assertEquals(AttendanceCorrectionStatus::Pending, $correction->status);
    }

    /* -------------------------------- Override --------------------------------- */

    public function test_configurator_can_unlock_a_locked_session(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $admin = $this->userWithRole(RoleName::SchoolAdmin->value);
        $session = $this->markLockedSession($teacher);

        $this->actingAs($admin)
            ->post(route('attendance.override', $session))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $session->refresh();
        $this->assertTrue($session->isOpen());
        $this->assertNull($session->locked_at);
    }

    public function test_a_teacher_cannot_override_a_locked_session(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $session = $this->markLockedSession($teacher);

        $this->actingAs($teacher)
            ->post(route('attendance.override', $session))
            ->assertForbidden();

        $session->refresh();
        $this->assertTrue($session->isLocked());
    }

    public function test_locked_session_cannot_be_edited_directly_after_close(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $session = $this->markLockedSession($teacher);

        $this->actingAs($teacher)
            ->put(route('attendance.update', $session), [
                'records' => [
                    ['student_id' => $this->firstStudent->getKey(), 'status' => 'present'],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('records');
    }

    /* ---------------------------------- Alerts --------------------------------- */

    public function test_alerts_page_flags_students_past_the_thresholds(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);

        for ($i = 0; $i < 3; $i++) {
            $this->markLockedSession($admin, 'absent', 'present', Carbon::today()->subDays($i));
        }

        $this->actingAs($admin)
            ->get(route('attendance.alerts'))
            ->assertOk()
            ->assertSee($this->firstStudent->full_name)
            ->assertSee('Absenteeism');
    }

    /* --------------------------------- Settings -------------------------------- */

    public function test_configurator_can_save_attendance_settings(): void
    {
        $admin = $this->userWithRole(RoleName::Principal->value);

        $this->actingAs($admin)
            ->put(route('attendance.settings.update'), [
                'attendance_mode' => 'daily',
                'start_time' => '08:30',
                'end_time' => '15:30',
                'late_threshold_minutes' => 15,
                'excused_counts_as_present' => false,
                'absence_alert_threshold' => 5,
                'late_alert_threshold' => 8,
                'parent_absence_notification' => true,
                'correction_approval_required' => true,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('08:30', Setting::value('start_time'));
        $this->assertSame('15', Setting::value('late_threshold_minutes'));

        $this->actingAs($admin)
            ->get(route('attendance.settings'))
            ->assertOk()
            ->assertSee('Attendance rules');
    }

    public function test_a_teacher_cannot_open_or_save_attendance_settings(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);

        $this->actingAs($teacher)
            ->get(route('attendance.settings'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->put(route('attendance.settings.update'), [
                'attendance_mode' => 'period',
                'start_time' => '08:00',
                'end_time' => '15:00',
                'late_threshold_minutes' => 10,
                'absence_alert_threshold' => 3,
                'late_alert_threshold' => 5,
            ])
            ->assertForbidden();
    }

    /* ------------------------------- Lock timestamps ---------------------------- */

    public function test_closing_a_session_records_lock_timestamps(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $session = $this->openSession($teacher);

        $this->actingAs($teacher)
            ->post(route('attendance.close', $session))
            ->assertRedirect();

        $session->refresh();

        $this->assertNotNull($session->submitted_at);
        $this->assertNotNull($session->locked_at);
        $this->assertEquals(AttendanceSessionStatus::Closed, $session->status);
    }
}

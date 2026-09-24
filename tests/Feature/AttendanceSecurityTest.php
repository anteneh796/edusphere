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
use App\Domains\Students\Models\Student;
use App\Support\Enums\AttendanceCorrectionStatus;
use App\Support\Enums\AttendanceSessionStatus;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceSecurityTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $year;

    private ClassRoom $firstClass;

    private ClassRoom $secondClass;

    private Student $firstStudent;

    private Student $secondClassStudent;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            RoleName::SuperAdmin->value => 'Super Admin',
            RoleName::Teacher->value => 'Teacher',
        ] as $name => $label) {
            Role::create(['name' => $name, 'label' => $label]);
        }

        $this->year = AcademicYear::factory()->current()->create();

        $grade = GradeLevel::create([
            'name' => 'Grade 5',
            'code' => '5',
            'sort_order' => 6,
        ]);

        $this->firstClass = ClassRoom::create([
            'grade_level_id' => $grade->getKey(),
            'academic_year_id' => $this->year->getKey(),
            'name' => '5 A',
            'capacity' => 40,
        ]);

        $this->secondClass = ClassRoom::create([
            'grade_level_id' => $grade->getKey(),
            'academic_year_id' => $this->year->getKey(),
            'name' => '5 B',
            'capacity' => 40,
        ]);

        $this->firstStudent = $this->enrollStudent($this->firstClass, 'ES-26-1001');
        $this->secondClassStudent = $this->enrollStudent($this->secondClass, 'ES-26-1002');
    }

    public function test_teacher_only_sees_sessions_for_assigned_classes(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $this->assignTeacher($teacher, $this->firstClass);

        $admin = $this->userWithRole(RoleName::SuperAdmin->value);

        (new AttendanceService)->openSession($this->firstClass, Carbon::today(), $admin->getKey());
        (new AttendanceService)->openSession($this->secondClass, Carbon::today(), $admin->getKey());

        $this->actingAs($teacher)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('5 A')
            ->assertDontSee('5 B');
    }

    public function test_teacher_cannot_open_an_unassigned_class_from_the_admin_attendance_screen(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $this->assignTeacher($teacher, $this->firstClass);

        $this->actingAs($teacher)
            ->post(route('attendance.store'), [
                'class_room_id' => $this->secondClass->getKey(),
                'date' => Carbon::today()->toDateString(),
            ])
            ->assertForbidden();

        $this->assertSame(0, AttendanceSession::count());
    }

    public function test_teacher_cannot_view_administrative_attendance_reports(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);

        $this->actingAs($teacher)
            ->get(route('attendance.reports.daily'))
            ->assertForbidden();
    }

    public function test_future_attendance_date_is_rejected(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);

        $this->actingAs($admin)
            ->post(route('attendance.store'), [
                'class_room_id' => $this->firstClass->getKey(),
                'date' => Carbon::tomorrow()->toDateString(),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('date');

        $this->assertSame(0, AttendanceSession::count());
    }

    public function test_attendance_cannot_be_marked_for_a_student_from_another_class(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $this->assignTeacher($teacher, $this->firstClass);

        $session = (new AttendanceService)->openSession(
            $this->firstClass,
            Carbon::today(),
            $teacher->getKey()
        );

        $this->actingAs($teacher)
            ->put(route('attendance.update', $session), [
                'records' => [
                    [
                        'student_id' => $this->secondClassStudent->getKey(),
                        'status' => 'present',
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('records');

        $this->assertSame(0, $session->records()->count());
    }

    public function test_duplicate_pending_correction_requests_are_rejected(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $this->assignTeacher($teacher, $this->firstClass);

        $session = (new AttendanceService)->openSession(
            $this->firstClass,
            Carbon::today(),
            $teacher->getKey()
        );

        (new AttendanceService)->upsertRecords($session, $teacher->getKey(), [
            ['student_id' => $this->firstStudent->getKey(), 'status' => 'absent'],
        ]);

        (new AttendanceService)->closeSession($session);

        $record = $this->firstStudent->attendanceRecords()->firstOrFail();
        $service = new AttendanceService;

        $service->requestCorrection($record, 'present', 'First request.', $teacher->getKey());

        $this->expectException(\DomainException::class);

        $service->requestCorrection($record, 'late', 'Second request.', $teacher->getKey());

        $this->assertSame(1, AttendanceCorrection::where('attendance_record_id', $record->getKey())->count());
    }

    public function test_correction_to_the_existing_status_is_rejected(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $this->assignTeacher($teacher, $this->firstClass);

        $session = (new AttendanceService)->openSession(
            $this->firstClass,
            Carbon::today(),
            $teacher->getKey()
        );

        (new AttendanceService)->upsertRecords($session, $teacher->getKey(), [
            ['student_id' => $this->firstStudent->getKey(), 'status' => 'present'],
        ]);

        (new AttendanceService)->closeSession($session);

        $record = $this->firstStudent->attendanceRecords()->firstOrFail();

        $this->expectException(\DomainException::class);

        (new AttendanceService)->requestCorrection(
            $record,
            'present',
            'No actual change.',
            $teacher->getKey()
        );
    }

    public function test_review_service_refuses_already_reviewed_correction(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);
        $this->assignTeacher($teacher, $this->firstClass);

        $session = (new AttendanceService)->openSession(
            $this->firstClass,
            Carbon::today(),
            $teacher->getKey()
        );

        (new AttendanceService)->upsertRecords($session, $teacher->getKey(), [
            ['student_id' => $this->firstStudent->getKey(), 'status' => 'absent'],
        ]);

        (new AttendanceService)->closeSession($session);

        $record = $this->firstStudent->attendanceRecords()->firstOrFail();
        $service = new AttendanceService;
        $correction = $service->requestCorrection(
            $record,
            'present',
            'Was present.',
            $teacher->getKey()
        );

        $service->reviewCorrection($correction, true, $admin->getKey());

        $correction->refresh();
        $this->assertEquals(AttendanceCorrectionStatus::Approved, $correction->status);

        $this->expectException(\DomainException::class);

        $service->reviewCorrection($correction, false, $admin->getKey());
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $roleName)->firstOrFail());

        return $user;
    }

    private function assignTeacher(User $teacher, ClassRoom $class): void
    {
        $subject = Subject::create([
            'name' => 'Mathematics '.$class->name,
            'code' => 'MAT-'.$class->name,
        ]);

        ClassSubject::create([
            'class_room_id' => $class->getKey(),
            'subject_id' => $subject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'periods_per_week' => 5,
            'position' => 1,
            'is_homeroom' => true,
        ]);
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
}

<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Students\Models\Student;
use App\Support\Enums\AttendanceSessionStatus;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceModuleTest extends TestCase
{
    use RefreshDatabase;

    private string $classId;

    private Student $firstStudent;

    private Student $secondStudent;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => RoleName::SuperAdmin->value, 'label' => 'Super Admin']);
        Role::create(['name' => RoleName::Principal->value, 'label' => 'Principal']);
        Role::create(['name' => RoleName::Registrar->value, 'label' => 'Registrar']);
        Role::create(['name' => RoleName::Teacher->value, 'label' => 'Teacher']);
        Role::create(['name' => RoleName::FinanceOfficer->value, 'label' => 'Finance Officer']);

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

    protected function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $roleName)->first());

        return $user;
    }

    protected function enrollStudent(ClassRoom $class, string $number): Student
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

    public function test_teacher_can_open_a_session_and_mark_records(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);

        $this->actingAs($teacher)
            ->post(route('attendance.store'), [
                'class_room_id' => $this->classId,
                'date' => now()->toDateString(),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $session = AttendanceSession::where('class_room_id', $this->classId)->firstOrFail();

        $this->actingAs($teacher)
            ->put(route('attendance.update', $session), [
                'records' => [
                    ['student_id' => $this->firstStudent->getKey(), 'status' => 'present'],
                    ['student_id' => $this->secondStudent->getKey(), 'status' => 'absent', 'note' => 'Sick'],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->getKey(),
            'student_id' => $this->firstStudent->getKey(),
            'status' => 'present',
        ]);
        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->getKey(),
            'student_id' => $this->secondStudent->getKey(),
            'status' => 'absent',
            'note' => 'Sick',
        ]);

        $this->actingAs($teacher)
            ->get(route('attendance.show', $session))
            ->assertOk()
            ->assertSee('Marking board');
    }

    public function test_opening_the_same_class_and_date_reuses_the_session(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $date = now()->toDateString();

        $this->actingAs($teacher)->post(route('attendance.store'), [
            'class_room_id' => $this->classId,
            'date' => $date,
        ]);

        $this->actingAs($teacher)->post(route('attendance.store'), [
            'class_room_id' => $this->classId,
            'date' => $date,
        ])->assertRedirect();

        $this->assertSame(1, AttendanceSession::where('class_room_id', $this->classId)->whereDate('date', $date)->count());
    }

    public function test_records_are_upserted_when_marks_are_resubmitted(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $session = $this->makeSession($teacher);

        $payload = [
            'records' => [
                ['student_id' => $this->firstStudent->getKey(), 'status' => 'present'],
                ['student_id' => $this->secondStudent->getKey(), 'status' => 'late'],
            ],
        ];

        $this->actingAs($teacher)->put(route('attendance.update', $session), $payload)->assertSessionHasNoErrors();
        $this->actingAs($teacher)->put(route('attendance.update', $session), $payload)->assertSessionHasNoErrors();

        $this->assertSame(2, AttendanceRecord::where('attendance_session_id', $session->getKey())->count());
    }

    public function test_closed_session_cannot_be_edited(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $session = $this->makeSession($teacher);
        $session->update([
            'status' => AttendanceSessionStatus::Closed->value,
            'closed_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->put(route('attendance.update', $session), [
                'records' => [
                    ['student_id' => $this->firstStudent->getKey(), 'status' => 'absent'],
                ],
            ])
            ->assertSessionHasErrors('records');

        $this->assertDatabaseMissing('attendance_records', ['attendance_session_id' => $session->getKey()]);
    }

    public function test_finance_officer_is_denied_access_to_attendance(): void
    {
        $financeOfficer = $this->userWithRole(RoleName::FinanceOfficer->value);

        $this->actingAs($financeOfficer)
            ->get(route('attendance.index'))
            ->assertForbidden();
    }

    public function test_offline_sync_pushes_records_by_student_number_and_is_idempotent(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $date = now()->subDay()->toDateString();

        $payload = [
            'records' => [
                [
                    'client_id' => (string) Str::uuid(),
                    'class_room_id' => $this->classId,
                    'date' => $date,
                    'student_number' => $this->firstStudent->student_number,
                    'status' => 'present',
                ],
                [
                    'client_id' => (string) Str::uuid(),
                    'class_room_id' => $this->classId,
                    'date' => $date,
                    'student_number' => $this->secondStudent->student_number,
                    'status' => 'excused',
                    'note' => 'Clinic visit',
                ],
            ],
        ];

        $this->actingAs($teacher)
            ->post('/api/v1/attendance/sync', $payload)
            ->assertOk()
            ->assertJson(['records' => 2, 'sessions' => 1]);

        $session = AttendanceSession::where('class_room_id', $this->classId)->whereDate('date', $date)->firstOrFail();

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->getKey(),
            'student_id' => $this->secondStudent->getKey(),
            'status' => 'excused',
            'note' => 'Clinic visit',
        ]);

        $this->actingAs($teacher)
            ->post('/api/v1/attendance/sync', $payload)
            ->assertOk()
            ->assertJson(['records' => 2, 'sessions' => 1]);

        $this->assertSame(2, AttendanceRecord::where('attendance_session_id', $session->getKey())->count());
    }

    public function test_session_can_be_closed(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $session = $this->makeSession($teacher);

        $this->actingAs($teacher)
            ->post(route('attendance.close', $session))
            ->assertRedirect();

        $session->refresh();

        $this->assertEquals(AttendanceSessionStatus::Closed, $session->status);
        $this->assertNotNull($session->closed_at);
    }

    private function makeSession(User $user): AttendanceSession
    {
        return AttendanceSession::create([
            'class_room_id' => $this->classId,
            'academic_year_id' => AcademicYear::current()->firstOrFail()->getKey(),
            'taken_by_id' => $user->getKey(),
            'date' => now()->toDateString(),
            'status' => AttendanceSessionStatus::Open->value,
            'opened_at' => now(),
        ]);
    }
}

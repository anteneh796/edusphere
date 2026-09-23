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
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Students\Models\Student;
use App\Support\Enums\AttendanceSessionStatus;
use App\Support\Enums\RoleName;
use Database\Seeders\DemoAttendanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoAttendanceSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => RoleName::SuperAdmin->value, 'label' => 'Super Admin']);
        Role::create(['name' => RoleName::Teacher->value, 'label' => 'Teacher']);

        $year = AcademicYear::factory()->current()->create();
        $grade = GradeLevel::create(['name' => 'Grade 5', 'code' => '5', 'sort_order' => 6]);
        $class = ClassRoom::create([
            'grade_level_id' => $grade->getKey(),
            'academic_year_id' => $year->getKey(),
            'name' => '5 A',
            'capacity' => 40,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $student = Student::factory()->create([
                'student_number' => 'ES-TEST-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'grade_level_id' => $grade->getKey(),
                'class_room_id' => $class->getKey(),
                'academic_year_id' => $year->getKey(),
            ]);

            $student->enrollments()->create([
                'academic_year_id' => $year->getKey(),
                'grade_level_id' => $grade->getKey(),
                'class_room_id' => $class->getKey(),
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
        }

        $teacher = User::factory()->create();
        $teacher->roles()->attach(Role::where('name', RoleName::Teacher->value)->first());
        $subject = Subject::create(['name' => 'Mathematics', 'code' => 'MATH']);

        ClassSubject::create([
            'class_room_id' => $class->getKey(),
            'subject_id' => $subject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'periods_per_week' => 5,
            'position' => 1,
            'is_homeroom' => true,
        ]);
    }

    public function test_seeder_creates_locked_history_containing_the_class(): void
    {
        (new DemoAttendanceSeeder)->run();

        $this->assertGreaterThanOrEqual(4, AttendanceSession::count());
        $this->assertGreaterThanOrEqual(3, AttendanceRecord::count());

        $this->assertSame(1, AttendanceSession::whereDate('date', now()->toDateString())->count());

        foreach (AttendanceSession::where('status', AttendanceSessionStatus::Closed->value)->get() as $session) {
            $this->assertNotNull($session->submitted_at);
            $this->assertNotNull($session->locked_at);
        }

        $this->assertNotSame(0, AttendanceSession::where('status', AttendanceSessionStatus::Open->value)->count());
    }

    public function test_seeder_is_idempotent(): void
    {
        (new DemoAttendanceSeeder)->run();
        $sessions = AttendanceSession::count();
        $records = AttendanceRecord::count();

        (new DemoAttendanceSeeder)->run();

        $this->assertSame($sessions, AttendanceSession::count());
        $this->assertSame($records, AttendanceRecord::count());
    }

    public function test_seeder_creates_a_pending_correction(): void
    {
        (new DemoAttendanceSeeder)->run();

        $this->assertGreaterThanOrEqual(1, AttendanceCorrection::where('status', 'pending')->count());
    }
}

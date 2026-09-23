<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    private string $gradeId;

    private string $classId;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => RoleName::SuperAdmin->value, 'label' => 'Super Admin']);
        Role::create(['name' => RoleName::Registrar->value, 'label' => 'Registrar']);
        Role::create(['name' => RoleName::Teacher->value, 'label' => 'Teacher']);

        AcademicYear::factory()->current()->create();

        $grade = GradeLevel::create(['name' => 'Grade 5', 'code' => '5', 'sort_order' => 6]);
        $this->gradeId = $grade->getKey();

        $class = ClassRoom::create([
            'grade_level_id' => $grade->getKey(),
            'academic_year_id' => AcademicYear::current()->firstOrFail()->getKey(),
            'name' => '5 A',
            'capacity' => 40,
        ]);
        $this->classId = $class->getKey();
    }

    protected function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $roleName)->first());

        return $user;
    }

    protected function studentPayload(string $nationalId = 'ET-48271234'): array
    {
        return [
            'first_name' => 'Tewodros',
            'last_name' => 'Gebre',
            'gender' => 'male',
            'date_of_birth' => '2014-03-12',
            'national_id' => $nationalId,
            'enrollment_date' => '2026-09-10',
            'grade_level_id' => $this->gradeId,
            'class_room_id' => $this->classId,
            'guardian' => [
                'first_name' => 'Gennet',
                'last_name' => 'Gebre',
                'relationship' => 'mother',
                'phone' => '+251911223344',
            ],
        ];
    }

    public function test_registrar_can_register_a_student_with_guardian_and_enrollment(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);

        $this->actingAs($registrar)
            ->post(route('students.store'), $this->studentPayload())
            ->assertRedirect(route('students.show', Student::where('last_name', 'Gebre')->firstOrFail()));

        $student = Student::where('last_name', 'Gebre')->first();

        $this->assertNotNull($student);
        $this->assertSame('new', $student->status);
        $this->assertMatchesRegularExpression('/^BMA\d{2}\d[A-Z]\d{3}$/', $student->student_number);
        $this->assertSame('BMA265A001', $student->student_number);
        $this->assertSame($this->gradeId, $student->grade_level_id);
        $this->assertSame($this->classId, $student->class_room_id);

        $guardian = Guardian::where('last_name', 'Gebre')->first();
        $this->assertNotNull($guardian);
        $this->assertTrue($student->guardians()->where('guardians.id', $guardian->getKey())->wherePivot('is_primary', true)->exists());

        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->getKey(),
            'academic_year_id' => AcademicYear::current()->firstOrFail()->getKey(),
            'grade_level_id' => $this->gradeId,
            'status' => 'active',
        ]);
    }

    public function test_teacher_cannot_register_students_but_can_browse(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);

        $this->actingAs($teacher)
            ->get(route('students.create'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->post(route('students.store'), $this->studentPayload())
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('students.index'))
            ->assertOk();
    }

    public function test_national_id_must_be_unique(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);

        $this->actingAs($registrar)
            ->post(route('students.store'), $this->studentPayload())
            ->assertRedirect();

        $this->actingAs($registrar)
            ->post(route('students.store'), $this->studentPayload())
            ->assertSessionHasErrors('national_id');
    }

    public function test_super_admin_can_update_student_status_and_guardian(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);
        $student = Student::factory()->create([
            'grade_level_id' => $this->gradeId,
            'class_room_id' => $this->classId,
            'status' => 'new',
        ]);

        $this->actingAs($admin)
            ->put(route('students.update', $student), [
                ...$this->studentPayload('ET-99998888'),
                'status' => 'active',
                'guardian' => ['first_name' => 'Updated', 'last_name' => 'Guardian', 'relationship' => 'father', 'phone' => '+251900000001'],
            ])
            ->assertRedirect(route('students.show', $student));

        $this->assertSame('active', $student->fresh()->status);
        $this->assertSame('Updated', $student->fresh()->primaryGuardian->first_name);
    }

    public function test_super_admin_can_archive_a_student(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);
        $student = Student::factory()->create([
            'grade_level_id' => $this->gradeId,
            'class_room_id' => $this->classId,
        ]);

        $this->actingAs($admin)
            ->delete(route('students.destroy', $student))
            ->assertRedirect(route('students.index'));

        $this->assertSoftDeleted('students', ['id' => $student->getKey()]);
    }
}

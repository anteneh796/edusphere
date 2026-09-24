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

class GuardiansModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            RoleName::SuperAdmin->value => 'Super Admin',
            RoleName::Principal->value => 'Principal',
            RoleName::Registrar->value => 'Registrar',
            RoleName::Teacher->value => 'Teacher',
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

    private function studentUnit(): Student
    {
        $year = AcademicYear::factory()->current()->create();
        $grade = GradeLevel::create(['name' => 'Grade 5', 'code' => '5', 'sort_order' => 6]);
        $class = ClassRoom::factory()->create([
            'academic_year_id' => $year->getKey(),
            'grade_level_id' => $grade->getKey(),
        ]);

        return Student::factory()->create([
            'class_room_id' => $class->getKey(),
            'academic_year_id' => $class->academic_year_id,
            'grade_level_id' => $grade->getKey(),
        ]);
    }

    public function test_principal_can_open_the_guardian_form(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);

        $this->actingAs($principal)
            ->get(route('guardians.create'))
            ->assertOk()
            ->assertSee('Register guardian');
    }

    public function test_principal_can_register_a_guardian(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);

        $this->actingAs($principal)
            ->post(route('guardians.store'), [
                'first_name' => 'Almaz',
                'last_name' => 'Bekele',
                'relationship' => 'mother',
                'phone' => '+251911000000',
                'email' => 'almaz@edu.et',
                'occupation' => 'Nurse',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('guardians', ['email' => 'almaz@edu.et']);
        $this->assertDatabaseHas('audit_logs', ['module' => 'guardians']);
    }

    public function test_teacher_can_view_the_guardians_list(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        Guardian::factory()->create(['first_name' => 'Almaz', 'last_name' => 'Bekele']);

        $this->actingAs($teacher)
            ->get(route('guardians.index'))
            ->assertOk()
            ->assertSee('Almaz Bekele');
    }


    public function test_guardian_registration_links_a_student(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);
        $student = $this->studentUnit();

        $this->actingAs($principal)
            ->post(route('guardians.store'), [
                'first_name' => 'Kebede',
                'last_name' => 'Haile',
                'relationship' => 'father',
                'phone' => '+251911111111',
                'student_ids' => [$student->getKey()],
            ])
            ->assertRedirect();

        $guardian = Guardian::where('email', null)->latest()->first();
        $this->assertDatabaseHas('guardian_student', ['guardian_id' => $guardian->getKey(), 'student_id' => $student->getKey()]);
    }

    public function test_registrar_can_update_a_guardian(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $guardian = Guardian::factory()->create();

        $this->actingAs($registrar)
            ->put(route('guardians.update', $guardian), [
                'first_name' => $guardian->first_name,
                'last_name' => $guardian->last_name,
                'relationship' => $guardian->relationship,
                'phone' => '+251922222222',
            ])
            ->assertRedirect(route('guardians.show', $guardian));

        $this->assertDatabaseHas('guardians', ['id' => $guardian->getKey(), 'phone' => '+251922222222']);
    }

    public function test_registrar_cannot_delete_a_guardian(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $guardian = Guardian::factory()->create();

        $this->actingAs($registrar)
            ->delete(route('guardians.destroy', $guardian))
            ->assertForbidden();

        $this->assertDatabaseHas('guardians', ['id' => $guardian->getKey()]);
    }

    public function test_principal_can_delete_a_guardian_and_unlink_students(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);
        $guardian = Guardian::factory()->create();
        $student = $this->studentUnit();
        $student->guardian_id = $guardian->getKey();
        $student->save();
        $guardian->students()->attach($student->getKey(), ['is_primary' => true]);

        $this->actingAs($principal)
            ->delete(route('guardians.destroy', $guardian))
            ->assertRedirect(route('guardians.index'));

        $this->assertSoftDeleted('guardians', ['id' => $guardian->getKey()]);
        $this->assertDatabaseMissing('guardian_student', ['guardian_id' => $guardian->getKey()]);
        $this->assertDatabaseHas('students', ['id' => $student->getKey(), 'guardian_id' => null]);
    }
}

<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicsModuleTest extends TestCase
{
    use RefreshDatabase;

    private string $gradeId;

    private string $classId;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => RoleName::SuperAdmin->value, 'label' => 'Super Admin']);
        Role::create(['name' => RoleName::Principal->value, 'label' => 'Principal']);
        Role::create(['name' => RoleName::Registrar->value, 'label' => 'Registrar']);
        Role::create(['name' => RoleName::Teacher->value, 'label' => 'Teacher']);

        AcademicYear::factory()->current()->create();

        $grade = GradeLevel::create(['name' => 'Grade 5', 'code' => '5', 'sort_order' => 6]);
        $this->gradeId = $grade->getKey();

        $this->classId = ClassRoom::create([
            'grade_level_id' => $grade->getKey(),
            'academic_year_id' => AcademicYear::current()->firstOrFail()->getKey(),
            'name' => '5 A',
            'capacity' => 40,
        ])->getKey();
    }

    protected function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $roleName)->first());

        return $user;
    }

    protected function scienceSubject(string $code = 'GSCI'): Subject
    {
        return Subject::create(['name' => 'General Science', 'code' => $code, 'sort_order' => 4]);
    }

    public function test_principal_can_create_subjects_and_assign_with_a_teacher(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);
        $teacher = $this->userWithRole(RoleName::Teacher->value);

        $this->actingAs($principal)
            ->post(route('academics.subjects.store'), [
                'name' => 'General Science',
                'code' => 'GSCI',
                'description' => 'Integrated science.',
                'sort_order' => 4,
            ])
            ->assertRedirect(route('academics.subjects.index'))
            ->assertSessionHasNoErrors();

        $subject = Subject::where('code', 'GSCI')->firstOrFail();

        $this->actingAs($principal)
            ->put(route('academics.classes.subjects.update', $this->classId), [
                'subjects' => [
                    ['subject_id' => $subject->getKey(), 'teacher_id' => $teacher->getKey(), 'periods_per_week' => 5],
                ],
            ])
            ->assertRedirect(route('academics.classes.subjects', $this->classId))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('class_subject', [
            'class_room_id' => $this->classId,
            'subject_id' => $subject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'periods_per_week' => 5,
            'position' => 0,
        ]);
    }

    public function test_assignments_are_removed_when_dropped_from_the_class(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);
        $dropThis = $this->scienceSubject('ABIO');
        $keep = Subject::create(['name' => 'Biology', 'code' => 'BIO', 'sort_order' => 9]);

        $dropThis->assignments()->create(['class_room_id' => $this->classId, 'position' => 0]);
        $keep->assignments()->create(['class_room_id' => $this->classId, 'position' => 1]);

        $this->actingAs($principal)
            ->put(route('academics.classes.subjects.update', $this->classId), [
                'subjects' => [
                    ['subject_id' => $keep->getKey(), 'teacher_id' => null, 'periods_per_week' => null],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('class_subject', ['class_room_id' => $this->classId, 'subject_id' => $dropThis->getKey()]);
        $this->assertDatabaseHas('class_subject', ['class_room_id' => $this->classId, 'subject_id' => $keep->getKey()]);
    }

    public function test_registrar_can_view_the_hub_but_cannot_manage_structure(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);

        $this->actingAs($registrar)
            ->get(route('academics.index'))
            ->assertOk();

        $this->actingAs($registrar)
            ->post(route('academics.subjects.store'), [
                'name' => 'General Science',
                'code' => 'GSCI',
            ])
            ->assertForbidden();

        $this->actingAs($registrar)
            ->post(route('academics.classes.store'), [
                'grade_level_id' => $this->gradeId,
                'academic_year_id' => AcademicYear::current()->firstOrFail()->getKey(),
                'name' => '5 B',
                'capacity' => 40,
            ])
            ->assertForbidden();
    }

    public function test_subject_code_and_name_must_be_unique(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);
        $this->scienceSubject();

        $this->actingAs($principal)
            ->post(route('academics.subjects.store'), [
                'name' => 'General Science',
                'code' => 'GSCI',
            ])
            ->assertSessionHasErrors(['name', 'code']);

        $this->actingAs($principal)
            ->post(route('academics.subjects.store'), [
                'name' => 'General Sci 2',
                'code' => 'GISU',
                'sort_order' => 5,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_subject_that_is_assigned_cannot_be_deleted(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);
        $subject = $this->scienceSubject();
        $subject->assignments()->create(['class_room_id' => $this->classId, 'position' => 0]);

        $this->actingAs($principal)
            ->delete(route('academics.subjects.destroy', $subject))
            ->assertForbidden();

        $this->assertDatabaseHas('subjects', ['id' => $subject->getKey()]);
    }

    public function test_duplicate_class_name_for_same_grade_and_year_is_rejected(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);
        $yearId = AcademicYear::current()->firstOrFail()->getKey();

        $this->actingAs($principal)
            ->post(route('academics.classes.store'), [
                'grade_level_id' => $this->gradeId,
                'academic_year_id' => $yearId,
                'name' => '5 A',
                'capacity' => 40,
            ])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseMissing('class_rooms', ['name' => '5 B', 'grade_level_id' => $this->gradeId]);
    }
}

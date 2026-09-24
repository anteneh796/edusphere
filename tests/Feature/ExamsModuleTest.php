<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Exams\Models\ExamSubject;
use App\Domains\Students\Models\Student;
use App\Support\Enums\ExamStatus;
use App\Support\Enums\ExamType;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExamsModuleTest extends TestCase
{
    use RefreshDatabase;

    private string $classId;

    private string $subjectId;

    private Student $firstStudent;

    private Student $secondStudent;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => RoleName::SuperAdmin->value, 'label' => 'Super Admin']);
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

        $this->subjectId = Subject::create(['name' => 'Mathematics', 'code' => 'MATH'])->getKey();

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

    protected function makeExam(string $status = ExamStatus::Draft->value): Exam
    {
        return Exam::create([
            'academic_year_id' => AcademicYear::current()->firstOrFail()->getKey(),
            'created_by_id' => $this->userWithRole(RoleName::Principal->value)->getKey(),
            'name' => 'Midterm Examination',
            'type' => ExamType::Midterm->value,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'status' => $status,
        ]);
    }

    protected function makePaper(Exam $exam): ExamSubject
    {
        return ExamSubject::create([
            'exam_id' => $exam->getKey(),
            'class_room_id' => $this->classId,
            'subject_id' => $this->subjectId,
            'max_marks' => 100,
            'pass_marks' => 50,
        ]);
    }

    public function test_registrar_can_create_an_exam_and_redirects_to_it(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);

        $this->actingAs($registrar)
            ->post(route('exams.store'), [
                'academic_year_id' => AcademicYear::current()->firstOrFail()->getKey(),
                'name' => 'Term One Exam',
                'type' => ExamType::Term->value,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('exams', [
            'name' => 'Term One Exam',
            'type' => ExamType::Term->value,
            'status' => ExamStatus::Draft->value,
        ]);
    }

    public function test_teacher_can_view_the_exams_index(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $exam = $this->makeExam();

        $this->actingAs($teacher)
            ->get(route('exams.index'))
            ->assertOk()
            ->assertSee('Midterm Examination');
    }

    public function test_teacher_cannot_create_an_exam(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);

        $this->actingAs($teacher)
            ->post(route('exams.store'), [
                'name' => 'Unauthorized Exam',
                'type' => ExamType::Quiz->value,
                'start_date' => now()->toDateString(),
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('exams', ['name' => 'Unauthorized Exam']);
    }


    public function test_authorized_user_assigns_papers_and_reassignment_updates_them(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $exam = $this->makeExam();
        $subject = Subject::create(['name' => 'English', 'code' => 'ENG']);

        $payload = fn ($max) => ['papers' => [
            ['class_room_id' => $this->classId, 'subject_id' => $this->subjectId, 'max_marks' => $max, 'pass_marks' => 50],
            ['class_room_id' => $this->classId, 'subject_id' => $subject->getKey(), 'max_marks' => 60, 'pass_marks' => 30],
        ]];

        $this->actingAs($registrar)
            ->put(route('exams.papers.update', $exam), $payload(100))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(2, ExamSubject::where('exam_id', $exam->getKey())->count());

        $this->actingAs($registrar)
            ->put(route('exams.papers.update', $exam), $payload(120))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, ExamSubject::where('exam_id', $exam->getKey())->count());
        $this->assertDatabaseHas('exam_subjects', [
            'exam_id' => $exam->getKey(),
            'subject_id' => $this->subjectId,
            'max_marks' => 120,
        ]);
    }

    public function test_dropping_a_paper_from_the_form_deletes_it(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $exam = $this->makeExam();
        $this->makePaper($exam);

        $this->actingAs($registrar)
            ->put(route('exams.papers.update', $exam), [
                'papers' => [],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, ExamSubject::where('exam_id', $exam->getKey())->count());
    }

    public function test_teacher_can_view_the_results_board_for_a_paper(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $exam = $this->makeExam(ExamStatus::Published->value);
        $paper = $this->makePaper($exam);

        $this->actingAs($teacher)
            ->get(route('exams.results', $paper))
            ->assertOk()
            ->assertDontSee('Grading scale')
            ->assertSee('max 100 marks');
    }

    public function test_entering_results_stores_marks_and_entered_by(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $exam = $this->makeExam(ExamStatus::Published->value);
        $paper = $this->makePaper($exam);

        $this->actingAs($teacher)
            ->put(route('exams.results.save', $paper), [
                'results' => [
                    ['student_id' => $this->firstStudent->getKey(), 'marks_obtained' => 85, 'remarks' => 'Excellent'],
                    ['student_id' => $this->secondStudent->getKey(), 'marks_obtained' => 33],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $first = ExamResult::where('student_id', $this->firstStudent->getKey())->firstOrFail();

        $this->assertSame('85.00', $first->marks_obtained);
        $this->assertSame('Excellent', $first->remarks);
        $this->assertSame($teacher->getKey(), $first->entered_by_id);
        $this->assertFalse(Schema::hasColumn('exam_results', 'grade'));

        $second = ExamResult::where('student_id', $this->secondStudent->getKey())->firstOrFail();
        $this->assertSame('33.00', $second->marks_obtained);
    }

    public function test_results_are_upserted_when_resubmitted(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $exam = $this->makeExam(ExamStatus::Published->value);
        $paper = $this->makePaper($exam);

        $payload = ['results' => [
            ['student_id' => $this->firstStudent->getKey(), 'marks_obtained' => 70],
            ['student_id' => $this->secondStudent->getKey(), 'marks_obtained' => 55],
        ]];

        $this->actingAs($teacher)->put(route('exams.results.save', $paper), $payload)->assertSessionHasNoErrors();
        $this->actingAs($teacher)->put(route('exams.results.save', $paper), $payload)->assertSessionHasNoErrors();

        $this->assertSame(2, ExamResult::where('exam_subject_id', $paper->getKey())->count());
    }

    public function test_marks_above_the_paper_maximum_are_rejected(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $exam = $this->makeExam(ExamStatus::Published->value);
        $paper = $this->makePaper($exam);

        $this->actingAs($teacher)
            ->put(route('exams.results.save', $paper), [
                'results' => [
                    ['student_id' => $this->firstStudent->getKey(), 'marks_obtained' => 120],
                ],
            ])
            ->assertSessionHasErrors('results');

        $this->assertDatabaseMissing('exam_results', ['student_id' => $this->firstStudent->getKey()]);
    }

    public function test_principal_can_publish_and_complete_an_exam(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);
        $exam = $this->makeExam();

        $this->actingAs($principal)
            ->post(route('exams.publish', $exam))
            ->assertRedirect();

        $this->assertSame(ExamStatus::Published, $exam->fresh()->status);

        $this->actingAs($principal)
            ->post(route('exams.complete', $exam))
            ->assertRedirect();

        $this->assertSame(ExamStatus::Completed, $exam->fresh()->status);
    }

    public function test_registrar_cannot_delete_an_exam_but_principal_can(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $principal = $this->userWithRole(RoleName::Principal->value);
        $exam = $this->makeExam();

        $this->actingAs($registrar)
            ->delete(route('exams.destroy', $exam))
            ->assertForbidden();

        $this->assertModelExists($exam->fresh());

        $this->actingAs($principal)
            ->delete(route('exams.destroy', $exam))
            ->assertRedirect();

        $this->assertSoftDeleted($exam);
    }
}

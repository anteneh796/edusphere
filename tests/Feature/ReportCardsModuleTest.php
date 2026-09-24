<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicTerm;
use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Exams\Models\ExamSubject;
use App\Domains\Exams\Models\ReportCardItem;
use App\Domains\Exams\Services\ReportCardsService;
use App\Domains\Students\Models\Student;
use App\Support\Enums\ExamStatus;
use App\Support\Enums\ReportCardStatus;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardsModuleTest extends TestCase
{
    use RefreshDatabase;

    private string $classId;

    private string $subjectId;

    private Student $firstStudent;

    private ReportCardsService $cards;

    private Exam $exam;

    private ExamSubject $paper;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (RoleName::cases() as $role) {
            Role::create(['name' => $role->value, 'label' => $role->label()]);
        }

        $year = AcademicYear::factory()->create(['is_current' => true]);
        $term = AcademicTerm::factory()->create(['academic_year_id' => $year->getKey()]);
        $grade = GradeLevel::create(['name' => 'Grade 5', 'code' => '5']);
        $class = ClassRoom::create([
            'grade_level_id' => $grade->getKey(),
            'academic_year_id' => $year->getKey(),
            'name' => '5 A',
            'capacity' => 40,
        ]);

        $this->classId = $class->getKey();
        $this->subjectId = Subject::create(['name' => 'Mathematics', 'code' => 'MATH'])->getKey();

        $this->firstStudent = $this->enrollStudent($class, 'RC-26-0001');
        $this->cards = app(ReportCardsService::class);
        $this->exam = $this->makeExam($year);
        $this->paper = $this->makePaper($year, $this->exam);
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

    protected function makeExam(AcademicYear $year): Exam
    {
        return Exam::create([
            'academic_year_id' => $year->getKey(),
            'name' => 'Midterm Examination',
            'type' => 'midterm',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'status' => ExamStatus::Published->value,
        ]);
    }

    protected function makePaper(AcademicYear $year, Exam $exam): ExamSubject
    {
        return ExamSubject::create([
            'exam_id' => $exam->getKey(),
            'subject_id' => $this->subjectId,
            'class_room_id' => $this->classId,
            'position' => 1,
            'max_marks' => 100,
            'pass_marks' => 50,
        ]);
    }

    public function test_generation_snapshots_total_average_and_class_rank(): void
    {
        ExamResult::create([
            'exam_subject_id' => $this->paper->getKey(),
            'student_id' => $this->firstStudent->getKey(),
            'marks_obtained' => 85,
        ]);

        $card = $this->cards->generateForStudent($this->exam, $this->firstStudent->getKey());

        $this->assertSame(ReportCardStatus::Generated, $card->status);
        $this->assertSame('85.00', (string) $card->total_obtained_marks);
        $this->assertSame('85.00', (string) $card->average_percent);
        $this->assertSame(1, $card->class_rank);
        $this->assertSame(1, $card->class_size);
        $this->assertDatabaseHas('report_cards', [
            'student_id' => $this->firstStudent->getKey(),
            'total_max_marks' => '100.00',
            'total_obtained_marks' => '85.00',
            'average_percent' => '85.00',
            'class_rank' => 1,
            'class_size' => 1,
        ]);

        $this->assertSame(1, ReportCardItem::where('report_card_id', $card->getKey())->count());
    }

    public function test_class_rank_uses_standard_competition_ranking_for_ties(): void
    {
        $marks = [95, 88, 88, 80];

        $students = collect(['Sara', 'Abel', 'Hana', 'Noah'])->map(fn (string $name, int $i) => $this->enrollStudent(
            $this->paper->classRoom,
            'RC-26-000'.($i + 2),
        ));

        $students->each(fn (Student $student, int $i) => ExamResult::create([
            'exam_subject_id' => $this->paper->getKey(),
            'student_id' => $student->getKey(),
            'marks_obtained' => $marks[$i],
        ]));

        foreach ([1, 2, 2, 4] as $i => $expectedRank) {
            $result = $this->cards->classRank($this->exam, $students[$i]);

            $this->assertSame($expectedRank, $result['rank']);
            $this->assertSame(4, $result['size']);
        }
    }

    public function test_report_cards_index_is_reachable_but_generation_requires_exam_edit_access(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);

        $this->actingAs($teacher)
            ->get(route('report-cards.index'))
            ->assertOk()
            ->assertSee('Report Cards');

        $this->actingAs($teacher)
            ->get(route('report-cards.create'))
            ->assertForbidden();

        $principal = $this->userWithRole(RoleName::Principal->value);

        $this->actingAs($principal)
            ->get(route('report-cards.create'))
            ->assertOk()
            ->assertSee('Midterm Examination');
    }

    public function test_approve_and_publish_workflow_requires_the_right_permissions(): void
    {
        ExamResult::create([
            'exam_subject_id' => $this->paper->getKey(),
            'student_id' => $this->firstStudent->getKey(),
            'marks_obtained' => 85,
        ]);

        $card = $this->cards->generateForStudent($this->exam, $this->firstStudent->getKey());

        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $this->actingAs($teacher)
            ->post(route('report-cards.approve', $card))
            ->assertForbidden();
        $this->actingAs($teacher)
            ->post(route('report-cards.publish', $card))
            ->assertForbidden();

        $principal = $this->userWithRole(RoleName::Principal->value);
        $this->actingAs($principal)
            ->post(route('report-cards.approve', $card))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(ReportCardStatus::Approved, $card->fresh()->status);

        $this->actingAs($principal)
            ->post(route('report-cards.publish', $card))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(ReportCardStatus::Published, $card->fresh()->status);
    }
}

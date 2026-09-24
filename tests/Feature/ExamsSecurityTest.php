<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ExamSubject;
use App\Domains\Students\Models\Student;
use App\Support\Enums\ExamStatus;
use App\Support\Enums\ExamType;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamsSecurityTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $year;
    private ClassRoom $class;
    private ClassRoom $otherClass;
    private Subject $subject;
    private Student $student;
    private Student $otherStudent;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (RoleName::cases() as $role) {
            Role::create(['name' => $role->value, 'label' => $role->label()]);
        }

        $this->year = AcademicYear::factory()->current()->create();

        $grade = GradeLevel::create([
            'name' => 'Grade 5',
            'code' => '5',
            'sort_order' => 5,
        ]);

        $this->class = ClassRoom::create([
            'grade_level_id' => $grade->getKey(),
            'academic_year_id' => $this->year->getKey(),
            'name' => '5 A',
            'capacity' => 40,
        ]);

        $this->otherClass = ClassRoom::create([
            'grade_level_id' => $grade->getKey(),
            'academic_year_id' => $this->year->getKey(),
            'name' => '5 B',
            'capacity' => 40,
        ]);

        $this->subject = Subject::create([
            'name' => 'Mathematics',
            'code' => 'MATH',
        ]);

        $this->student = $this->enroll($this->class, 'SEC-0001');
        $this->otherStudent = $this->enroll($this->otherClass, 'SEC-0002');
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $role)->first());

        return $user;
    }

    private function enroll(ClassRoom $class, string $number): Student
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

    private function exam(string $status = ExamStatus::Published->value): Exam
    {
        return Exam::create([
            'academic_year_id' => $this->year->getKey(),
            'name' => 'Security Midterm',
            'type' => ExamType::Midterm->value,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'status' => $status,
        ]);
    }

    private function paper(Exam $exam, ?ClassRoom $class = null): ExamSubject
    {
        return ExamSubject::create([
            'exam_id' => $exam->getKey(),
            'class_room_id' => ($class ?? $this->class)->getKey(),
            'subject_id' => $this->subject->getKey(),
            'max_marks' => 100,
            'pass_marks' => 50,
        ]);
    }

    public function test_students_and_parents_cannot_enter_exam_results(): void
    {
        $exam = $this->exam();
        $paper = $this->paper($exam);

        foreach ([RoleName::Student->value, RoleName::Parent->value] as $role) {
            $this->actingAs($this->user($role))
                ->get(route('exams.results', $paper))
                ->assertForbidden();
        }
    }

    public function test_teacher_can_enter_results_only_for_an_assigned_class_subject(): void
    {
        $teacher = $this->user(RoleName::Teacher->value);
        $exam = $this->exam();
        $paper = $this->paper($exam);

        $this->actingAs($teacher)
            ->get(route('exams.results', $paper))
            ->assertForbidden();

        ClassSubject::create([
            'class_room_id' => $this->class->getKey(),
            'subject_id' => $this->subject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'periods_per_week' => 5,
            'position' => 1,
        ]);

        $this->actingAs($teacher)
            ->get(route('exams.results', $paper))
            ->assertOk()
            ->assertSee('max 100 marks');
    }

    public function test_teacher_cannot_submit_a_result_for_a_student_from_another_class(): void
    {
        $teacher = $this->user(RoleName::Teacher->value);
        $exam = $this->exam();
        $paper = $this->paper($exam);

        ClassSubject::create([
            'class_room_id' => $this->class->getKey(),
            'subject_id' => $this->subject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'periods_per_week' => 5,
            'position' => 1,
        ]);

        $this->actingAs($teacher)
            ->put(route('exams.results.save', $paper), [
                'results' => [
                    ['student_id' => $this->otherStudent->getKey(), 'marks_obtained' => 80],
                ],
            ])
            ->assertSessionHasErrors('results');

        $this->assertDatabaseMissing('exam_results', [
            'exam_subject_id' => $paper->getKey(),
            'student_id' => $this->otherStudent->getKey(),
        ]);
    }

    public function test_results_cannot_be_entered_before_exam_is_published(): void
    {
        $teacher = $this->user(RoleName::Teacher->value);
        $exam = $this->exam(ExamStatus::Draft->value);
        $paper = $this->paper($exam);

        ClassSubject::create([
            'class_room_id' => $this->class->getKey(),
            'subject_id' => $this->subject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'periods_per_week' => 5,
            'position' => 1,
        ]);

        $this->actingAs($teacher)
            ->put(route('exams.results.save', $paper), [
                'results' => [
                    ['student_id' => $this->student->getKey(), 'marks_obtained' => 80],
                ],
            ])
            ->assertSessionHasErrors('results');

        $this->assertDatabaseMissing('exam_results', [
            'exam_subject_id' => $paper->getKey(),
            'student_id' => $this->student->getKey(),
        ]);
    }

    public function test_report_card_generation_rejects_an_exam_that_is_not_published(): void
    {
        $principal = $this->user(RoleName::Principal->value);
        $exam = $this->exam(ExamStatus::Draft->value);
        $this->paper($exam);

        $this->actingAs($principal)
            ->post(route('report-cards.store'), [
                'exam_id' => $exam->getKey(),
                'student_id' => $this->student->getKey(),
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseMissing('report_cards', [
            'exam_id' => $exam->getKey(),
            'student_id' => $this->student->getKey(),
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Services\AttendanceService;
use App\Domains\Students\Models\Student;
use App\Domains\TeacherPortal\Models\AssessmentResult;
use App\Domains\TeacherPortal\Models\BehaviorNote;
use App\Domains\TeacherPortal\Models\ClassroomAssessment;
use App\Domains\TeacherPortal\Models\CurriculumUnit;
use App\Domains\TeacherPortal\Models\HomeworkAssignment;
use App\Domains\TeacherPortal\Models\LessonPlan;
use App\Domains\TeacherPortal\Models\TeachingResource;
use App\Domains\TeacherPortal\Models\TimetableSlot;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TeacherPortalTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function teacherUser(): User
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $user->roles()->attach(
            Role::where('name', RoleName::Teacher->value)->firstOrFail()
        );

        return $user;
    }

    public function test_teacher_can_open_dashboard(): void
    {
        $this->actingAs($this->teacherUser())
            ->get(route('cms.teacher.dashboard'))
            ->assertOk()
            ->assertSee(__('Teacher dashboard'));
    }

    public function test_teacher_can_open_all_readonly_portal_pages(): void
    {
        $this->actingAs($this->teacherUser());

        $pages = [
            'timetable',
            'classes',
            'attendance',
            'lesson-plans',
            'curriculum',
            'homework',
            'assessments',
            'behavior',
            'progress',
            'homeroom',
            'messages',
            'resources',
            'reports',
        ];

        foreach ($pages as $page) {
            $this->get(route("cms.teacher.$page"))->assertOk();
        }
    }

    public function test_teacher_can_open_class_detail(): void
    {
        $user = $this->teacherUser();
        $this->actingAs($user);

        $academicYear = AcademicYear::current()->firstOrFail();
        $gradeLevel = GradeLevel::where('code', '5')->firstOrFail();
        $classRoom = ClassRoom::factory()->create([
            'academic_year_id' => $academicYear->getKey(),
            'grade_level_id' => $gradeLevel->getKey(),
            'name' => '5 C',
        ]);
        $subject = Subject::factory()->create();
        $classSubject = ClassSubject::create([
            'class_room_id' => $classRoom->getKey(),
            'subject_id' => $subject->getKey(),
            'teacher_id' => $user->getKey(),
        ]);

        $this->get(route('cms.teacher.classes.show', $classSubject))->assertOk();
    }

    /* ------------------------------- Helpers -------------------------------- */

    private function makeClass(User $teacher, string $label = '5 C', bool $homeroom = false): array
    {
        $year = AcademicYear::current()->firstOrFail();
        $grade = GradeLevel::where('code', '5')->firstOrFail();

        $classRoom = ClassRoom::create([
            'academic_year_id' => $year->getKey(),
            'grade_level_id' => $grade->getKey(),
            'name' => $label,
            'capacity' => 40,
        ]);

        $subject = Subject::factory()->create();

        $classSubject = ClassSubject::create([
            'class_room_id' => $classRoom->getKey(),
            'subject_id' => $subject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'is_homeroom' => $homeroom,
        ]);

        return [$classRoom, $subject, $classSubject];
    }

    private function makeStudent(ClassRoom $classRoom): Student
    {
        return Student::create([
            'student_number' => 'ES-'.fake()->unique()->numerify('####'),
            'first_name' => 'Test',
            'last_name' => 'Student',
            'gender' => 'female',
            'date_of_birth' => now()->subYears(11)->toDateString(),
            'status' => 'active',
            'enrollment_date' => now()->toDateString(),
            'class_room_id' => $classRoom->getKey(),
            'grade_level_id' => $classRoom->grade_level_id,
            'academic_year_id' => $classRoom->academic_year_id,
        ]);
    }

    /* --------------------------------- Tests --------------------------------- */

    public function test_dashboard_is_scoped_to_assigned_classes(): void
    {
        $teacherA = $this->teacherUser();
        $teacherB = $this->teacherUser();

        [, $subjectA] = $this->makeClass($teacherA, '5 D');
        [, $subjectB] = $this->makeClass($teacherB, '5 E');
        $subjectA->update(['name' => 'Alpha Algebra']);
        $subjectB->update(['name' => 'Gamma Geometry']);

        $this->actingAs($teacherA)
            ->get(route('cms.teacher.dashboard'))
            ->assertOk()
            ->assertSee('Alpha Algebra')
            ->assertDontSee('Gamma Geometry');
    }

    public function test_teacher_cannot_view_another_teachers_class(): void
    {
        $teacherA = $this->teacherUser();
        [, , $classSubject] = $this->makeClass($teacherA, '5 D');

        $this->actingAs($this->teacherUser())
            ->get(route('cms.teacher.classes.show', $classSubject))
            ->assertForbidden();
    }

    public function test_teacher_profile_renders_assignment_counts(): void
    {
        $teacher = $this->teacherUser();
        $teacher->forceFill([
            'staff_type' => 'teacher',
            'department' => 'Academics',
            'job_title' => 'Senior Teacher',
            'employee_id' => 'T-001',
        ])->save();
        [, , $classSubject] = $this->makeClass($teacher, '5 C');

        LessonPlan::create([
            'class_subject_id' => $classSubject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'topic' => 'Fractions',
            'scheduled_date' => now()->toDateString(),
        ]);
        HomeworkAssignment::create([
            'class_subject_id' => $classSubject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'title' => 'Fractions worksheet',
            'assigned_on' => now()->toDateString(),
            'due_on' => now()->addDays(3)->toDateString(),
            'status' => 'draft',
        ]);
        ClassroomAssessment::create([
            'class_subject_id' => $classSubject->getKey(),
            'class_room_id' => $classSubject->class_room_id,
            'teacher_id' => $teacher->getKey(),
            'title' => 'Chapter 1 quiz',
            'status' => 'draft',
            'total_marks' => 20,
        ]);

        $this->actingAs($teacher)
            ->get(route('cms.teacher.profile'))
            ->assertOk()
            ->assertSee($teacher->full_name);
    }

    public function test_timetable_shows_slots_for_teachers_classes(): void
    {
        $teacher = $this->teacherUser();
        [$classRoom, $subject, $classSubject] = $this->makeClass($teacher, '5 C');

        TimetableSlot::create([
            'class_room_id' => $classRoom->getKey(),
            'class_subject_id' => $classSubject->getKey(),
            'academic_year_id' => AcademicYear::current()->firstOrFail()->getKey(),
            'day_of_week' => 1,
            'period_number' => 1,
            'room' => 'R1',
        ]);

        $this->actingAs($teacher)
            ->get(route('cms.teacher.timetable'))
            ->assertOk()
            ->assertSee($subject->name)
            ->assertSee('R1');
    }

    public function test_teacher_attendance_lifecycle_open_mark_close(): void
    {
        $teacher = $this->teacherUser();
        [$classRoom] = $this->makeClass($teacher, '5 C');
        $student = $this->makeStudent($classRoom);

        $this->actingAs($teacher)
            ->post(route('cms.teacher.attendance.store'), [
                'class_room_id' => $classRoom->getKey(),
                'date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $session = AttendanceSession::where('class_room_id', $classRoom->getKey())
            ->whereDate('date', now()->toDateString())
            ->firstOrFail();

        $this->get(route('cms.teacher.attendance.session', $session))
            ->assertOk()
            ->assertSee($student->full_name);

        $this->put(route('cms.teacher.attendance.update', $session), [
            'records' => [
                ['student_id' => $student->getKey(), 'status' => 'present', 'note' => ''],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->getKey(),
            'student_id' => $student->getKey(),
            'status' => 'present',
        ]);

        $this->post(route('cms.teacher.attendance.close', $session))->assertRedirect();

        $this->assertDatabaseHas('attendance_sessions', [
            'id' => $session->getKey(),
            'status' => 'closed',
        ]);
    }

    public function test_teacher_cannot_mark_a_closed_session(): void
    {
        $teacher = $this->teacherUser();
        [$classRoom] = $this->makeClass($teacher, '5 C');
        $student = $this->makeStudent($classRoom);

        $session = (new AttendanceService)->openSession($classRoom, now()->toDateString(), $teacher->getKey());
        (new AttendanceService)->closeSession($session);

        $this->actingAs($teacher)
            ->put(route('cms.teacher.attendance.update', $session), [
                'records' => [
                    ['student_id' => $student->getKey(), 'status' => 'present', 'note' => ''],
                ],
            ])
            ->assertSessionHasErrors('records');
    }

    public function test_teacher_cannot_manage_others_attendance_session(): void
    {
        $teacherA = $this->teacherUser();
        [$classRoom] = $this->makeClass($teacherA, '5 D');

        $session = (new AttendanceService)->openSession($classRoom, now()->toDateString(), $teacherA->getKey());

        $this->actingAs($this->teacherUser())
            ->get(route('cms.teacher.attendance.session', $session))
            ->assertForbidden();

        $this->actingAs($this->teacherUser())
            ->put(route('cms.teacher.attendance.update', $session), [
                'records' => [],
            ])
            ->assertForbidden();
    }

    public function test_teacher_cannot_open_attendance_for_others_class(): void
    {
        $teacherA = $this->teacherUser();
        [$classRoom] = $this->makeClass($teacherA, '5 D');

        $this->actingAs($this->teacherUser())
            ->post(route('cms.teacher.attendance.store'), [
                'class_room_id' => $classRoom->getKey(),
                'date' => now()->toDateString(),
            ])
            ->assertSessionHasErrors('class_room_id');
    }

    public function test_lesson_plans_are_scoped_and_owned(): void
    {
        $teacher = $this->teacherUser();
        [, , $classSubject] = $this->makeClass($teacher, '5 C');

        $this->actingAs($teacher)
            ->post(route('cms.teacher.lesson-plans.store'), [
                'class_subject_id' => $classSubject->getKey(),
                'topic' => 'Fractions',
                'scheduled_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lesson_plans', [
            'class_subject_id' => $classSubject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'topic' => 'Fractions',
        ]);

        $lessonPlan = LessonPlan::where('class_subject_id', $classSubject->getKey())->firstOrFail();

        $this->actingAs($this->teacherUser())
            ->get(route('cms.teacher.lesson-plans.edit', $lessonPlan))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->delete(route('cms.teacher.lesson-plans.destroy', $lessonPlan))
            ->assertRedirect();

        $this->assertDatabaseMissing('lesson_plans', ['id' => $lessonPlan->getKey()]);
    }

    public function test_lesson_plan_store_rejects_others_class_subject(): void
    {
        $teacherA = $this->teacherUser();
        [, , $classSubject] = $this->makeClass($teacherA, '5 D');

        $this->actingAs($this->teacherUser())
            ->post(route('cms.teacher.lesson-plans.store'), [
                'class_subject_id' => $classSubject->getKey(),
                'topic' => 'Nope',
            ])
            ->assertSessionHasErrors('class_subject_id');
    }

    public function test_curriculum_unit_can_be_updated_by_owner(): void
    {
        $teacher = $this->teacherUser();
        [, , $classSubject] = $this->makeClass($teacher, '5 C');

        $unit = CurriculumUnit::create([
            'class_subject_id' => $classSubject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'title' => 'Unit 1',
            'position' => 1,
            'status' => 'pending',
        ]);

        $this->actingAs($teacher)
            ->put(route('cms.teacher.curriculum.update', $unit), [
                'title' => $unit->title,
                'position' => 1,
                'total_lessons' => 8,
                'covered_lessons' => 3,
                'status' => 'in_progress',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('curriculum_units', [
            'id' => $unit->getKey(),
            'covered_lessons' => 3,
            'status' => 'in_progress',
        ]);

        $this->actingAs($this->teacherUser())
            ->put(route('cms.teacher.curriculum.update', $unit), [
                'title' => $unit->title,
                'status' => 'completed',
            ])
            ->assertForbidden();
    }

    public function test_homework_publish_is_scoped_to_owner(): void
    {
        $teacher = $this->teacherUser();
        [, , $classSubject] = $this->makeClass($teacher, '5 C');

        $assignment = HomeworkAssignment::create([
            'class_subject_id' => $classSubject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'title' => 'Fractions worksheet',
            'assigned_on' => now()->toDateString(),
            'due_on' => now()->addDays(3)->toDateString(),
            'status' => 'draft',
        ]);

        $this->actingAs($this->teacherUser())
            ->post(route('cms.teacher.homework.publish', $assignment))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->post(route('cms.teacher.homework.publish', $assignment))
            ->assertRedirect();

        $this->assertDatabaseHas('homework_assignments', [
            'id' => $assignment->getKey(),
            'status' => 'published',
        ]);
    }

    public function test_homework_can_be_edited_updated_and_deleted_by_owner(): void
    {
        $teacher = $this->teacherUser();
        [, , $classSubject] = $this->makeClass($teacher, '5 D');

        $assignment = HomeworkAssignment::create([
            'class_subject_id' => $classSubject->getKey(),
            'teacher_id' => $teacher->getKey(),
            'title' => 'Fractions worksheet',
            'assigned_on' => now()->toDateString(),
            'due_on' => now()->addDays(3)->toDateString(),
            'max_marks' => 20,
            'status' => 'draft',
        ]);

        $this->actingAs($teacher)
            ->get(route('cms.teacher.homework.edit', $assignment))
            ->assertOk()
            ->assertSee('Fractions worksheet');

        $this->put(route('cms.teacher.homework.update', $assignment), [
            'class_subject_id' => $classSubject->getKey(),
            'title' => 'Fractions worksheet v2',
            'assigned_on' => now()->toDateString(),
            'due_on' => now()->addDays(5)->toDateString(),
            'max_marks' => 30,
            'status' => 'published',
        ])->assertRedirect();

        $this->assertDatabaseHas('homework_assignments', [
            'id' => $assignment->getKey(),
            'title' => 'Fractions worksheet v2',
            'max_marks' => 30,
            'status' => 'published',
        ]);

        $this->actingAs($teacher)
            ->delete(route('cms.teacher.homework.destroy', $assignment))
            ->assertRedirect();

        $this->assertDatabaseMissing('homework_assignments', ['id' => $assignment->getKey()]);
    }

    public function test_homework_edit_update_delete_rejected_for_other_teacher(): void
    {
        $teacherA = $this->teacherUser();
        [, , $classSubject] = $this->makeClass($teacherA, '5 D');

        $assignment = HomeworkAssignment::create([
            'class_subject_id' => $classSubject->getKey(),
            'teacher_id' => $teacherA->getKey(),
            'title' => 'Fractions worksheet',
            'assigned_on' => now()->toDateString(),
            'due_on' => now()->addDays(3)->toDateString(),
            'status' => 'draft',
        ]);

        $other = $this->teacherUser();

        $this->actingAs($other)
            ->get(route('cms.teacher.homework.edit', $assignment))
            ->assertForbidden();

        $this->actingAs($other)
            ->put(route('cms.teacher.homework.update', $assignment), [
                'class_subject_id' => $classSubject->getKey(),
                'title' => 'Hijacked',
            ])
            ->assertForbidden();

        $this->actingAs($other)
            ->delete(route('cms.teacher.homework.destroy', $assignment))
            ->assertForbidden();
    }

    public function test_teacher_can_create_assessment_for_own_class(): void
    {
        $teacher = $this->teacherUser();
        [$classRoom, , $classSubject] = $this->makeClass($teacher, '5 C');

        $this->actingAs($teacher)
            ->post(route('cms.teacher.assessments.store'), [
                'class_subject_id' => $classSubject->getKey(),
                'class_room_id' => $classRoom->getKey(),
                'title' => 'Chapter 1 quiz',
                'total_marks' => 20,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('classroom_assessments', [
            'class_subject_id' => $classSubject->getKey(),
            'class_room_id' => $classRoom->getKey(),
            'teacher_id' => $teacher->getKey(),
        ]);

        $this->actingAs($this->teacherUser())
            ->post(route('cms.teacher.assessments.store'), [
                'class_subject_id' => $classSubject->getKey(),
                'class_room_id' => $classRoom->getKey(),
                'title' => 'Cheat',
            ])
            ->assertSessionHasErrors('class_subject_id');
    }

    public function test_grades_record_and_validate_roster_and_max_marks(): void
    {
        $teacher = $this->teacherUser();
        [$classRoom, , $classSubject] = $this->makeClass($teacher, '5 C');
        $student = $this->makeStudent($classRoom);

        $assessment = ClassroomAssessment::create([
            'class_subject_id' => $classSubject->getKey(),
            'class_room_id' => $classRoom->getKey(),
            'teacher_id' => $teacher->getKey(),
            'title' => 'Chapter 2 quiz',
            'total_marks' => 100,
            'status' => 'published',
        ]);

        $this->actingAs($teacher)
            ->get(route('cms.teacher.assessments.grades', $assessment))
            ->assertOk()
            ->assertSee($student->full_name);

        $this->post(route('cms.teacher.assessments.grades.store', $assessment), [
            'student_id' => [$student->getKey()],
            'marks_obtained' => [$student->getKey() => 85],
        ])->assertRedirect();

        $this->assertDatabaseHas('assessment_results', [
            'classroom_assessment_id' => $assessment->getKey(),
            'student_id' => $student->getKey(),
            'marks_obtained' => 85,
        ]);

        $this->post(route('cms.teacher.assessments.grades.store', $assessment), [
            'student_id' => [$student->getKey()],
            'marks_obtained' => [$student->getKey() => 120],
        ])->assertSessionHasErrors('marks_obtained.'.$student->getKey());
    }

    public function test_teacher_cannot_grade_others_assessment(): void
    {
        $teacherA = $this->teacherUser();
        [, , $classSubject] = $this->makeClass($teacherA, '5 D');

        $assessment = ClassroomAssessment::create([
            'class_subject_id' => $classSubject->getKey(),
            'class_room_id' => $classSubject->class_room_id,
            'teacher_id' => $teacherA->getKey(),
            'title' => 'Other teacher quiz',
            'total_marks' => 50,
        ]);

        $this->actingAs($this->teacherUser())
            ->get(route('cms.teacher.assessments.grades', $assessment))
            ->assertForbidden();

        $this->actingAs($this->teacherUser())
            ->post(route('cms.teacher.assessments.grades.store', $assessment), [
                'student_id' => [],
            ])
            ->assertForbidden();
    }

    public function test_behavior_note_scoped_to_own_students(): void
    {
        $teacherA = $this->teacherUser();
        [$classRoom] = $this->makeClass($teacherA, '5 D');
        $student = $this->makeStudent($classRoom);

        $this->actingAs($teacherA)
            ->post(route('cms.teacher.behavior.store'), [
                'student_id' => $student->getKey(),
                'recorded_on' => now()->toDateString(),
                'note' => 'Helped a peer.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('behavior_notes', [
            'student_id' => $student->getKey(),
            'teacher_id' => $teacherA->getKey(),
        ]);

        $this->actingAs($this->teacherUser())
            ->post(route('cms.teacher.behavior.store'), [
                'student_id' => $student->getKey(),
                'recorded_on' => now()->toDateString(),
                'note' => 'Cannot record.',
            ])
            ->assertSessionHasErrors('student_id');
    }

    public function test_behavior_note_can_be_updated_and_deleted_by_owner(): void
    {
        $teacher = $this->teacherUser();
        [$classRoom] = $this->makeClass($teacher, '5 D');
        $student = $this->makeStudent($classRoom);

        $note = BehaviorNote::create([
            'student_id' => $student->getKey(),
            'teacher_id' => $teacher->getKey(),
            'type' => 'observation',
            'severity' => 'info',
            'recorded_on' => now()->toDateString(),
            'note' => 'Original note.',
        ]);

        $this->actingAs($teacher)
            ->get(route('cms.teacher.behavior.edit', $note))
            ->assertOk()
            ->assertSee('Original note.');

        $this->put(route('cms.teacher.behavior.update', $note), [
            'student_id' => $student->getKey(),
            'recorded_on' => now()->toDateString(),
            'note' => 'Updated note.',
            'severity' => 'warning',
        ])->assertRedirect();

        $this->assertDatabaseHas('behavior_notes', [
            'id' => $note->getKey(),
            'note' => 'Updated note.',
            'severity' => 'warning',
        ]);

        $this->actingAs($teacher)
            ->delete(route('cms.teacher.behavior.destroy', $note))
            ->assertRedirect();

        $this->assertDatabaseMissing('behavior_notes', ['id' => $note->getKey()]);
    }

    public function test_behavior_note_edit_update_delete_rejected_for_other_teacher(): void
    {
        $teacherA = $this->teacherUser();
        [$classRoom] = $this->makeClass($teacherA, '5 D');
        $student = $this->makeStudent($classRoom);

        $note = BehaviorNote::create([
            'student_id' => $student->getKey(),
            'teacher_id' => $teacherA->getKey(),
            'type' => 'observation',
            'severity' => 'info',
            'recorded_on' => now()->toDateString(),
            'note' => 'Private note.',
        ]);

        $other = $this->teacherUser();

        $this->actingAs($other)
            ->get(route('cms.teacher.behavior.edit', $note))
            ->assertForbidden();

        $this->actingAs($other)
            ->put(route('cms.teacher.behavior.update', $note), [
                'student_id' => $student->getKey(),
                'recorded_on' => now()->toDateString(),
                'note' => 'Hijacked.',
            ])
            ->assertForbidden();

        $this->actingAs($other)
            ->delete(route('cms.teacher.behavior.destroy', $note))
            ->assertForbidden();
    }

    public function test_homeroom_is_scoped_to_teacher_homeroom_class(): void
    {
        $teacherA = $this->teacherUser();
        [$classRoom] = $this->makeClass($teacherA, '5 D', homeroom: true);
        $student = $this->makeStudent($classRoom);

        $this->actingAs($teacherA)
            ->get(route('cms.teacher.homeroom'))
            ->assertOk()
            ->assertSee($student->full_name)
            ->assertSee('5 D');

        $this->actingAs($this->teacherUser())
            ->get(route('cms.teacher.homeroom'))
            ->assertOk()
            ->assertSee('No homeroom assigned');
    }

    public function test_messages_send_and_appear_in_inbox_and_outbox(): void
    {
        $teacherA = $this->teacherUser();
        $teacherB = $this->teacherUser();

        $this->actingAs($teacherA)
            ->post(route('cms.teacher.messages.store'), [
                'recipient_uid' => $teacherB->getKey(),
                'subject' => 'Staff assembly',
                'body' => 'Please join the staff room.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('teacher_messages', [
            'sender_id' => $teacherA->getKey(),
            'recipient_id' => $teacherB->getKey(),
            'message_type' => 'individual',
        ]);

        $this->actingAs($teacherB)
            ->get(route('cms.teacher.messages'))
            ->assertOk()
            ->assertSee('Staff assembly');

        $this->actingAs($teacherA)
            ->post(route('cms.teacher.messages.store'), [
                'recipient_uid' => $teacherA->getKey(),
                'subject' => 'Self',
                'body' => 'No.',
            ])
            ->assertForbidden();
    }

    public function test_resources_crud_is_scoped_to_owner(): void
    {
        $teacher = $this->teacherUser();
        [, , $classSubject] = $this->makeClass($teacher, '5 C');

        $resource = TeachingResource::create([
            'teacher_id' => $teacher->getKey(),
            'class_subject_id' => $classSubject->getKey(),
            'title' => 'Fractions worksheet',
        ]);

        $this->actingAs($teacher)
            ->get(route('cms.teacher.resources.edit', $resource))
            ->assertOk();

        $this->actingAs($teacher)
            ->put(route('cms.teacher.resources.update', $resource), [
                'class_subject_id' => $classSubject->getKey(),
                'title' => 'Fractions worksheet v2',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('teaching_resources', [
            'id' => $resource->getKey(),
            'title' => 'Fractions worksheet v2',
        ]);

        $this->actingAs($this->teacherUser())
            ->get(route('cms.teacher.resources.edit', $resource))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->delete(route('cms.teacher.resources.destroy', $resource))
            ->assertRedirect();

        $this->assertDatabaseMissing('teaching_resources', ['id' => $resource->getKey()]);
    }

    public function test_reports_render_scoped_analytics(): void
    {
        $teacher = $this->teacherUser();
        [$classRoom, , $classSubject] = $this->makeClass($teacher, '5 C');
        $student = $this->makeStudent($classRoom);

        $assessment = ClassroomAssessment::create([
            'class_subject_id' => $classSubject->getKey(),
            'class_room_id' => $classRoom->getKey(),
            'teacher_id' => $teacher->getKey(),
            'title' => 'Term exam',
            'total_marks' => 100,
            'status' => 'published',
        ]);

        AssessmentResult::create([
            'classroom_assessment_id' => $assessment->getKey(),
            'student_id' => $student->getKey(),
            'marks_obtained' => 60,
            'status' => 'entered',
            'entered_by_id' => $teacher->getKey(),
        ]);

        $this->actingAs($teacher)
            ->get(route('cms.teacher.reports'))
            ->assertOk()
            ->assertSee($assessment->title)
            ->assertSee('100%');
    }
}

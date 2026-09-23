<?php

namespace Database\Seeders;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\TeacherPortal\Models\ClassroomAssessment;
use App\Domains\TeacherPortal\Models\HomeworkAssignment;
use App\Domains\TeacherPortal\Models\LessonPlan;
use App\Domains\TeacherPortal\Models\TimetableSlot;
use App\Support\Enums\RoleName;
use Illuminate\Database\Seeder;

class DemoTeacherSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $role = Role::where('name', RoleName::Teacher->value)->first();

        if (! $currentYear || ! $role) {
            return;
        }

        $specs = [
            [
                'email' => 'demo.teacher1@edusphere.com',
                'first_name' => 'Alem',
                'last_name' => 'Molla',
                'employee_id' => 'T-1001',
                'department' => 'Academics',
                'job_title' => 'Mathematics Teacher',
                'phone' => '+251911101001',
                'hire_date' => '2019-09-02',
                'class' => ['5', 'A'],
                'subjects' => ['MATH', 'ENG'],
                'homeroom_on' => 'MATH',
                'lesson_topics' => ['Fractions and decimals', 'Multiplying whole numbers'],
                'homework_title' => 'Chapter 4 review exercises',
                'assessment_title' => 'Fractions unit quiz',
            ],
            [
                'email' => 'demo.teacher2@edusphere.com',
                'first_name' => 'Birtukan',
                'last_name' => 'Haile',
                'employee_id' => 'T-1002',
                'department' => 'Academics',
                'job_title' => 'Science Teacher',
                'phone' => '+251911102002',
                'hire_date' => '2021-01-11',
                'class' => ['6', 'A'],
                'subjects' => ['GSCI', 'HLTH'],
                'homeroom_on' => 'GSCI',
                'lesson_topics' => ['The water cycle', 'Food chains and ecosystems'],
                'homework_title' => 'Life processes worksheet',
                'assessment_title' => 'Ecosystems pop quiz',
            ],
            [
                'email' => 'demo.teacher3@edusphere.com',
                'first_name' => 'Yohannes',
                'last_name' => 'Bekele',
                'employee_id' => 'T-1003',
                'department' => 'Academics',
                'job_title' => 'Social Studies Teacher',
                'phone' => '+251911103003',
                'hire_date' => '2020-09-07',
                'class' => ['7', 'A'],
                'subjects' => ['SSTD', 'ICT'],
                'homeroom_on' => 'SSTD',
                'lesson_topics' => ['Regions of Ethiopia', 'Spreadsheets basics'],
                'homework_title' => 'Map skills assignment',
                'assessment_title' => 'Geography unit test',
            ],
        ];

        foreach ($specs as $spec) {
            $teacher = $this->createTeacher($spec);

            $class = ClassRoom::where('academic_year_id', $currentYear->getKey())
                ->where('name', $spec['class'][0].' '.$spec['class'][1])
                ->first();

            if (! $class) {
                continue;
            }

            $assignments = [];
            foreach ($spec['subjects'] as $position => $code) {
                $subject = Subject::where('code', $code)->first();

                if (! $subject) {
                    continue;
                }

                $assignments[] = ClassSubject::updateOrCreate(
                    ['class_room_id' => $class->getKey(), 'subject_id' => $subject->getKey()],
                    [
                        'teacher_id' => $teacher->getKey(),
                        'periods_per_week' => in_array($code, ['MATH', 'ENG'], true) ? 5 : 3,
                        'position' => $position,
                        'is_homeroom' => $code === $spec['homeroom_on'],
                    ]
                );
            }

            if ($assignments !== []) {
                $this->seedContent($teacher, $class, $assignments, $currentYear, $spec);
            }
        }
    }

    private function createTeacher(array $spec): User
    {
        $user = User::updateOrCreate(
            ['email' => $spec['email']],
            [
                'first_name' => $spec['first_name'],
                'last_name' => $spec['last_name'],
                'employee_id' => $spec['employee_id'],
                'phone' => $spec['phone'],
                'staff_type' => 'teacher',
                'department' => $spec['department'],
                'job_title' => $spec['job_title'],
                'hire_date' => $spec['hire_date'],
                'contract_end_date' => '2027-07-02',
                'password' => 'Dev@2026',
                'status' => 'active',
                'must_change_password' => false,
                'email_verified_at' => now(),
            ]
        );

        $role = Role::where('name', RoleName::Teacher->value)->first();

        if ($role && ! $user->roles()->where('role_id', $role->getKey())->exists()) {
            $user->roles()->attach($role);
        }

        return $user;
    }

    private function seedContent(User $teacher, ClassRoom $class, array $assignments, AcademicYear $year, array $spec): void
    {
        /** @var ClassSubject $primary */
        $primary = $assignments[0];

        foreach ($spec['lesson_topics'] as $index => $topic) {
            LessonPlan::firstOrCreate(
                [
                    'class_subject_id' => $primary->getKey(),
                    'teacher_id' => $teacher->getKey(),
                    'topic' => $topic,
                ],
                [
                    'week_number' => $index + 1,
                    'unit' => 'Unit '.($index + 1),
                    'scheduled_date' => now()->addWeeks($index)->format('Y-m-d'),
                    'status' => $index === 0 ? 'published' : 'draft',
                    'objectives' => 'Students will be able to apply the key concepts of this lesson.',
                    'materials' => 'Textbook, exercise sheets, whiteboard.',
                    'activities' => 'Guided practice followed by independent questions.',
                    'assessment' => 'Short exit ticket.',
                    'homework' => 'Complete two practice problems at home.',
                ]
            );
        }

        HomeworkAssignment::firstOrCreate(
            [
                'class_subject_id' => $primary->getKey(),
                'teacher_id' => $teacher->getKey(),
                'title' => $spec['homework_title'],
            ],
            [
                'instructions' => 'Complete the assigned exercises and submit before the due date.',
                'assigned_on' => now()->format('Y-m-d'),
                'due_on' => now()->addWeek()->format('Y-m-d'),
                'max_marks' => 20,
                'visibility' => 'class',
                'status' => 'published',
            ]
        );

        ClassroomAssessment::firstOrCreate(
            [
                'class_subject_id' => $primary->getKey(),
                'class_room_id' => $class->getKey(),
                'teacher_id' => $teacher->getKey(),
                'title' => $spec['assessment_title'],
            ],
            [
                'type' => 'quiz',
                'status' => 'published',
                'total_marks' => 100,
                'assessment_date' => now()->addDays(7)->format('Y-m-d'),
                'room' => $class->name,
                'instructions' => 'Answer all questions. Show your working where required.',
            ]
        );

        $days = [[1, 1], [3, 2], [5, 3]];

        foreach ($assignments as $slotIndex => $assignment) {
            [$day, $period] = $days[$slotIndex] ?? $days[0];

            TimetableSlot::updateOrCreate(
                [
                    'class_room_id' => $class->getKey(),
                    'day_of_week' => $day,
                    'period_number' => $period,
                ],
                [
                    'class_subject_id' => $assignment->getKey(),
                    'academic_year_id' => $year->getKey(),
                    'room' => $class->name,
                ]
            );
        }
    }
}

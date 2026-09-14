<?php

namespace Database\Seeders;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Exams\Models\ExamSubject;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use App\Domains\Students\Models\StudentEnrollment;
use App\Support\Enums\AttendanceSessionStatus;
use App\Support\Enums\AttendanceStatus;
use App\Support\Enums\ExamStatus;
use App\Support\Enums\ExamType;
use App\Support\Enums\GuardianRelationship;
use App\Support\Enums\RoleName;
use App\Support\Enums\StudentStatus;
use App\Support\GradeScale;
use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTeachers();
        $this->seedStaff();
        $this->seedPortalAccounts();
        $this->seedPortalAccounts();

        if (Student::count() === 0) {
            $this->seedStudents();
        }

        $this->seedSubjectAssignments();
        $this->seedAttendance();
        $this->seedExams();
    }

    private function seedExams(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->firstOrFail();

        $organizer = User::whereHas('roles', fn ($query) => $query->whereIn('name', [
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
            RoleName::Registrar->value,
        ]))->first();
        $teachers = User::whereHas('roles', fn ($query) => $query->where('name', RoleName::Teacher->value))->get();

        if (! $organizer || $teachers->isEmpty()) {
            return;
        }

        $midterm = Exam::updateOrCreate(
            ['academic_year_id' => $currentYear->getKey(), 'name' => 'Midterm Examination', 'type' => ExamType::Midterm->value],
            [
                'created_by_id' => $organizer->getKey(),
                'start_date' => now()->subDays(7)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
                'status' => ExamStatus::Published->value,
                'description' => 'First semester midterm assessment covering all enrolled subjects.',
            ]
        );

        Exam::updateOrCreate(
            ['academic_year_id' => $currentYear->getKey(), 'name' => 'Final Examination', 'type' => ExamType::Final->value],
            [
                'created_by_id' => $organizer->getKey(),
                'start_date' => now()->addMonths(3)->toDateString(),
                'end_date' => now()->addMonths(3)->addDays(10)->toDateString(),
                'status' => ExamStatus::Draft->value,
                'description' => 'End of academic year comprehensive examination.',
            ]
        );

        $classes = ClassRoom::with('students')
            ->where('academic_year_id', $currentYear->getKey())
            ->get()
            ->filter(fn (ClassRoom $class) => $class->students->isNotEmpty())
            ->take(3);

        $resultClass = $classes->sortByDesc(fn (ClassRoom $class) => $class->students->count())->first();

        foreach ($classes as $position => $class) {
            $subjects = ClassSubject::where('class_room_id', $class->getKey())
                ->orderBy('position')
                ->limit(4)
                ->with('subject')
                ->get()
                ->map(fn (ClassSubject $assignment) => $assignment->subject);

            foreach ($subjects as $index => $subject) {
                $paper = ExamSubject::updateOrCreate(
                    [
                        'exam_id' => $midterm->getKey(),
                        'class_room_id' => $class->getKey(),
                        'subject_id' => $subject->getKey(),
                    ],
                    [
                        'max_marks' => 100,
                        'pass_marks' => 50,
                        'exam_date' => now()->subDays(7)->addDays($index)->toDateString(),
                        'position' => $position * 10 + $index,
                        'instruction' => 'Answer all questions. Scientific calculators allowed.',
                    ]
                );

                if ($class->getKey() === $resultClass?->getKey()) {
                    foreach ($class->students as $student) {
                        $marks = fake()->randomFloat(0, 30, 99);

                        ExamResult::updateOrCreate(
                            ['exam_subject_id' => $paper->getKey(), 'student_id' => $student->getKey()],
                            [
                                'marks_obtained' => $marks,
                                'grade' => GradeScale::letter($marks),
                                'entered_by_id' => $teachers->random()->getKey(),
                            ]
                        );
                    }
                }
            }
        }
    }

    private function seedAttendance(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->firstOrFail();
        $users = User::whereHas('roles', fn ($query) => $query->whereIn('name', [
            RoleName::Principal->value,
            RoleName::Teacher->value,
        ]))->get();

        $classes = ClassRoom::with('students')
            ->where('academic_year_id', $currentYear->getKey())
            ->get()
            ->filter(fn (ClassRoom $class) => $class->students->isNotEmpty())
            ->take(3);

        foreach ($classes as $class) {
            foreach ([now()->subDay(), now()->subDays(2)] as $rawDate) {
                $date = $rawDate->startOfDay();

                $session = AttendanceSession::updateOrCreate(
                    ['class_room_id' => $class->getKey(), 'date' => $date],
                    [
                        'academic_year_id' => $currentYear->getKey(),
                        'taken_by_id' => $users->random()?->getKey(),
                        'status' => AttendanceSessionStatus::Closed->value,
                        'note' => null,
                        'opened_at' => $date->copy()->setTime(8, 15),
                        'closed_at' => $date->copy()->setTime(9, 0),
                    ]
                );

                foreach ($class->students as $student) {
                    AttendanceRecord::updateOrCreate(
                        ['attendance_session_id' => $session->getKey(), 'student_id' => $student->getKey()],
                        [
                            'status' => fake()->randomElement(AttendanceStatus::cases())->value,
                            'marked_by_id' => $session->taken_by_id,
                        ]
                    );
                }
            }
        }

        $target = $classes->sortByDesc(fn (ClassRoom $class) => $class->students->count())->first();

        if ($target) {
            $today = now()->startOfDay();

            $session = AttendanceSession::updateOrCreate(
                ['class_room_id' => $target->getKey(), 'date' => $today],
                [
                    'academic_year_id' => $currentYear->getKey(),
                    'taken_by_id' => $users->first()?->getKey(),
                    'status' => AttendanceSessionStatus::Open->value,
                    'note' => null,
                    'opened_at' => now(),
                    'closed_at' => null,
                ]
            );

            foreach ($target->students as $student) {
                AttendanceRecord::updateOrCreate(
                    ['attendance_session_id' => $session->getKey(), 'student_id' => $student->getKey()],
                    [
                        'status' => AttendanceStatus::Present->value,
                        'marked_by_id' => $session->taken_by_id,
                    ]
                );
            }
        }
    }

    private function seedTeachers(): void
    {
        $role = Role::where('name', RoleName::Teacher->value)->first();
        $teacherNames = [
            ['Mulugeta', 'Assefa'], ['Tigist', 'Shiferaw'], ['Daniel', 'Tamrat'],
            ['Worknesh', 'Gebre'], ['Tesfaye', 'Alemu'], ['Sofia', 'Legesse'],
            ['Getachew', 'Molla'], ['Azeb', 'Wondimu'],
        ];

        foreach ($teacherNames as $i => [$first, $last]) {
            $email = 'teacher'.($i + 1).'@edusphere.com';
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'password' => 'Dev@2026',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            if ($role && ! $user->roles()->where('role_id', $role->getKey())->exists()) {
                $user->roles()->attach($role);
            }
        }
    }

    private function seedStaff(): void
    {
        $staff = [
            ['email' => 'principal@edusphere.com', 'first_name' => 'Bereket', 'last_name' => 'Mengistu', 'role' => RoleName::Principal->value],
            ['email' => 'registrar@edusphere.com', 'first_name' => 'Hirut', 'last_name' => 'Lemma', 'role' => RoleName::Registrar->value],
            ['email' => 'accountant@edusphere.com', 'first_name' => 'Selamawit', 'last_name' => 'Haile', 'role' => RoleName::Accountant->value],
        ];

        foreach ($staff as $person) {
            $role = Role::where('name', $person['role'])->first();
            $user = User::updateOrCreate(
                ['email' => $person['email']],
                [
                    'first_name' => $person['first_name'],
                    'last_name' => $person['last_name'],
                    'password' => 'Dev@2026',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            if ($role && ! $user->roles()->where('role_id', $role->getKey())->exists()) {
                $user->roles()->attach($role);
            }
        }
    }

    private function seedSubjectAssignments(): void
    {
        $teachers = User::whereHas('roles', fn ($query) => $query->where('name', RoleName::Teacher->value))->get();

        if ($teachers->isEmpty()) {
            return;
        }

        $currentYear = AcademicYear::where('is_current', true)->firstOrFail();
        $classes = ClassRoom::where('academic_year_id', $currentYear->getKey())->with('gradeLevel')->get();

        foreach ($classes as $class) {
            foreach ($this->subjectsForGrade($class->gradeLevel->code) as $position => $code) {
                ClassSubject::firstOrCreate(
                    ['class_room_id' => $class->getKey(), 'subject_id' => Subject::where('code', $code)->value('id')],
                    [
                        'teacher_id' => $teachers->random()->getKey(),
                        'periods_per_week' => in_array($code, ['MATH', 'ENG'], true) ? 5 : 3,
                        'position' => $position,
                    ]
                );
            }
        }
    }

    private function subjectsForGrade(string $gradeCode): array
    {
        $foundation = ['MATH', 'ENG', 'AMH', 'GSCI', 'ENV', 'ART', 'MUS', 'PE'];

        $code = match (true) {
            in_array($gradeCode, ['KG1', 'KG2'], true) => 0,
            in_array($gradeCode, ['1', '2', '3', '4'], true) => 1,
            in_array($gradeCode, ['5', '6', '7', '8'], true) => 2,
            default => 3,
        };

        return match ($code) {
            1 => [...$foundation, 'SSTD', 'ICT'],
            2 => [...$foundation, 'SSTD', 'ICT', 'HOME', 'HLTH'],
            3 => ['MATH', 'ENG', 'AMH', 'PHY', 'CHEM', 'BIO', 'HIST', 'GEOG', 'CIVIC', 'ICT', 'PE'],
            default => $foundation,
        };
    }

    private function seedStudents(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->firstOrFail();
        $previousYear = AcademicYear::where('is_current', false)->latest('name')->firstOrFail();
        $grades = GradeLevel::ordered()->get();
        $classes = ClassRoom::where('academic_year_id', $currentYear->getKey())->get();

        $names = [
            ['Abebe', 'Kebede'], ['Sara', 'Tesfaye'], ['Dawit', 'Haile'],
            ['Hanna', 'Alemayehu'], ['Yonas', 'Getachew'], ['Meron', 'Tadesse'],
            ['Eden', 'Mekonnen'], ['Biruk', 'Fikadu'], ['Selam', 'Ayele'],
            ['Nahom', 'Belay'], ['Liya', 'Assefa'], ['Kalkidan', 'Worku'],
            ['Bethel', 'Girma'], ['Samson', 'Demissie'], ['Ruth', 'Tefera'],
            ['Natnael', 'Zewdu'], ['Marta', 'Solomon'], ['Henok', 'Desta'],
        ];

        $guardians = [];
        for ($i = 0; $i < 12; $i++) {
            $guardians[] = Guardian::create([
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'relationship' => fake()->randomElement(GuardianRelationship::cases())->value,
                'phone' => fake()->numerify('+2519########'),
                'email' => fake()->optional()->safeEmail(),
                'occupation' => fake()->optional()->jobTitle(),
                'address' => fake()->optional()->address(),
            ]);
        }

        foreach ($names as $index => [$first, $last]) {
            $grade = $grades->random();
            $class = $classes->where('grade_level_id', $grade->getKey())->random();

            $student = Student::create([
                'student_number' => 'ES-'.now()->format('y').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'first_name' => $first,
                'last_name' => $last,
                'gender' => fake()->randomElement(['male', 'female']),
                'date_of_birth' => fake()->dateTimeBetween('-17 years', '-7 years')->format('Y-m-d'),
                'status' => fake()->randomElement([StudentStatus::Active->value, StudentStatus::New->value]),
                'enrollment_date' => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
                'address' => fake()->optional()->address(),
                'grade_level_id' => $grade->getKey(),
                'class_room_id' => $class->getKey(),
                'academic_year_id' => $currentYear->getKey(),
            ]);

            StudentEnrollment::create([
                'student_id' => $student->getKey(),
                'academic_year_id' => $currentYear->getKey(),
                'grade_level_id' => $grade->getKey(),
                'class_room_id' => $class->getKey(),
                'status' => 'active',
                'enrolled_at' => $student->enrollment_date,
            ]);

            if (fake()->boolean(70)) {
                $previousGrade = $grades->firstWhere('sort_order', $grade->sort_order - 1)
                    ?? $grade;

                StudentEnrollment::create([
                    'student_id' => $student->getKey(),
                    'academic_year_id' => $previousYear->getKey(),
                    'grade_level_id' => $previousGrade->getKey(),
                    'class_room_id' => null,
                    'status' => 'left',
                    'enrolled_at' => fake()->dateTimeBetween('-2 years', '-1 year')->format('Y-m-d'),
                    'left_at' => fake()->dateTimeBetween('-1 year', '-2 months')->format('Y-m-d'),
                ]);
            }

            $guardian = $guardians[array_rand($guardians)];
            $student->guardians()->attach($guardian, ['is_primary' => true]);
            $student->update(['guardian_id' => $guardian->getKey()]);
        }
    }

    /**
     * Creates the portal demo accounts (roles already seeded by
     * RolesAndPermissionsSeeder) and links them to a real Student and
     * Guardian record so the student/parent portals resolve a profile.
     */
    private function seedPortalAccounts(): void
    {
        $studentRole = Role::where('name', RoleName::Student->value)->first();
        $parentRole = Role::where('name', RoleName::Parent->value)->first();

        $studentUser = User::updateOrCreate(
            ['email' => 'student@edusphere.com'],
            [
                'first_name' => 'Samrawit',
                'last_name' => 'Tadesse',
                'password' => 'Dev@2026',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        if ($studentRole && ! $studentUser->roles()->where('role_id', $studentRole->getKey())->exists()) {
            $studentUser->roles()->attach($studentRole);
        }

        $student = Student::query()->first();
        if ($student && ! $student->user_id && Schema::hasColumn('students', 'user_id')) {
            $student->update(['user_id' => $studentUser->getKey()]);
        }

        $parentUser = User::updateOrCreate(
            ['email' => 'parent@edusphere.com'],
            [
                'first_name' => 'Amanuel',
                'last_name' => 'Gebre',
                'password' => 'Dev@2026',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        if ($parentRole && ! $parentUser->roles()->where('role_id', $parentRole->getKey())->exists()) {
            $parentUser->roles()->attach($parentRole);
        }

        $guardian = Guardian::query()->first();
        if ($guardian && ! $guardian->user_id && Schema::hasColumn('guardians', 'user_id')) {
            $guardian->update(['user_id' => $parentUser->getKey()]);
        }
    }
}

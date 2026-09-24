<?php

namespace App\Domains\Admissions\Services;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Models\AdmissionAssessment;
use App\Domains\Admissions\Models\GradeCapacity;
use App\Domains\Cms\Models\Inquiry;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use App\Domains\Students\Models\StudentEnrollment;
use App\Domains\Students\Services\StudentService;
use App\Support\Enums\AdmissionAssessmentStatus;
use App\Support\Enums\AdmissionStatus;
use App\Support\Enums\RoleName;
use App\Support\Enums\StudentStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdmissionService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly StudentService $students,
    ) {}

    /* ------------------------------- Numbers ---------------------------------- */

    public function generateApplicationNumber(): string
    {
        $prefix = 'ADM-'.$this->yearLabel().'-';

        $last = AdmissionApplication::withTrashed()
            ->where('application_number', 'like', "{$prefix}%")
            ->orderByDesc('application_number')
            ->value('application_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function fallbackStudentNumber(?AcademicYear $year = null): string
    {
        $prefix = 'BG-'.$this->yearLabel($year).'-';

        $last = Student::withTrashed()
            ->where('student_number', 'like', "{$prefix}%")
            ->orderByDesc('student_number')
            ->value('student_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function yearLabel(?AcademicYear $year = null): string
    {
        $year ??= AcademicYear::current()->first()
            ?? AcademicYear::latest('start_date')->first();

        return $year?->start_date?->format('Y') ?? now()->format('Y');
    }

    /* ------------------------------ Applications ------------------------------ */

    public function createApplication(array $data, ?Inquiry $sourceInquiry = null, string $status = AdmissionStatus::Draft->value): AdmissionApplication
    {
        $application = DB::transaction(function () use ($data, $sourceInquiry, $status) {
            $guardians = $data['guardians'] ?? [];
            unset($data['guardians'], $data['source_inquiry_id']);
            $this->validatePlacement($data['grade_level_id'] ?? null, $data['intake_academic_year_id'] ?? $this->currentYearId());

            $application = AdmissionApplication::create([
                ...$data,
                'application_number' => $this->generateApplicationNumber(),
                'intake_academic_year_id' => $data['intake_academic_year_id'] ?? $this->currentYearId(),
                'status' => $sourceInquiry ? AdmissionStatus::Inquiry->value : $status,
                'source_inquiry_id' => $sourceInquiry?->getKey(),
                'applied_at' => in_array($status, [AdmissionStatus::Submitted->value, AdmissionStatus::Inquiry->value], true) ? now() : null,
                'created_by' => auth()->id(),
            ]);

            $this->syncGuardians($application, $guardians);

            return $application;
        });

        $this->notifications->sendToRoles(
            [RoleName::Registrar->value, RoleName::SchoolAdmin->value],
            $this->payload($application, 'admission', 'application',
                __('New admission application'),
                __(':name applied for grade :grade.', [
                    'name' => $application->full_name,
                    'grade' => $application->gradeLevel?->name ?? '—',
                ]))
        );

        return $application->fresh(['gradeLevel', 'intakeYear', 'primaryGuardian', 'sourceInquiry']);
    }
    public function createDraftFromInquiry(Inquiry $inquiry, string $gradeLevelId, string $academicYearId): AdmissionApplication
    {
        if ($inquiry->type !== 'admissions') {
            throw ValidationException::withMessages([
                'inquiry' => __('Only admissions inquiries can be converted into admission applications.'),
            ]);
        }

        if ($inquiry->handled_at || AdmissionApplication::where('source_inquiry_id', $inquiry->getKey())->exists()) {
            throw ValidationException::withMessages([
                'inquiry' => __('This inquiry has already been converted into an admission application.'),
            ]);
        }

        $grade = GradeLevel::query()->whereKey($gradeLevelId)->where('is_active', true)->first();
        $year = AcademicYear::query()->find($academicYearId);

        if (! $grade || $grade->stage === null) {
            throw ValidationException::withMessages([
                'grade_level_id' => __('Please select an active KG through Grade 8 admission grade.'),
            ]);
        }

        if (! $year) {
            throw ValidationException::withMessages([
                'intake_academic_year_id' => __('The selected academic year is invalid.'),
            ]);
        }

        $studentName = trim((string) ($inquiry->student_name ?: $inquiry->full_name));
        $parts = preg_split('/\\s+/', $studentName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstName = $parts[0] ?? 'Applicant';
        $lastName = count($parts) > 1 ? array_pop($parts) : $firstName;
        $otherNames = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

        $application = DB::transaction(function () use ($inquiry, $grade, $year, $firstName, $lastName, $otherNames) {
            $application = AdmissionApplication::create([
                'application_number' => $this->generateApplicationNumber(),
                'status' => AdmissionStatus::Draft->value,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'other_names' => $otherNames,
                'intake_academic_year_id' => $year->getKey(),
                'grade_level_id' => $grade->getKey(),
                'source_inquiry_id' => $inquiry->getKey(),
                'created_by' => auth()->id(),
            ]);

            if ($inquiry->full_name || $inquiry->email || $inquiry->phone) {
                $guardianParts = preg_split('/\\s+/', trim((string) $inquiry->full_name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $guardianFirst = $guardianParts[0] ?? 'Guardian';
                $guardianLast = count($guardianParts) > 1 ? implode(' ', array_slice($guardianParts, 1)) : $guardianFirst;

                $application->guardians()->create([
                    'first_name' => $guardianFirst,
                    'last_name' => $guardianLast,
                    'relationship' => 'guardian',
                    'phone' => $inquiry->phone,
                    'email' => $inquiry->email,
                    'is_primary' => true,
                ]);
            }

            $inquiry->update([
                'status' => 'handled',
                'handled_at' => now(),
                'handled_by' => auth()->id(),
            ]);

            return $application;
        });

        $this->notifications->sendToRoles(
            [RoleName::Registrar->value, RoleName::SchoolAdmin->value],
            $this->payload(
                $application,
                'admission',
                'application',
                __('Admission inquiry converted'),
                __('Inquiry for :name was converted to application :number.', [
                    'name' => $application->full_name,
                    'number' => $application->application_number,
                ])
            )
        );

        return $application->fresh(['gradeLevel', 'intakeYear', 'primaryGuardian', 'sourceInquiry']);
    }

    private function validatePlacement(?string $gradeLevelId, ?string $academicYearId): void
    {
        if ($gradeLevelId === null || $academicYearId === null) {
            throw ValidationException::withMessages([
                'placement' => __('An admission must have an intake academic year and an eligible KG through Grade 8 grade.'),
            ]);
        }

        $grade = GradeLevel::query()->whereKey($gradeLevelId)->first();
        if (! $grade || ! $grade->is_active || $grade->stage === null) {
            throw ValidationException::withMessages([
                'grade_level_id' => __('The selected grade is not an eligible EduSphere admission grade.'),
            ]);
        }

        if (! AcademicYear::query()->whereKey($academicYearId)->exists()) {
            throw ValidationException::withMessages([
                'intake_academic_year_id' => __('The selected academic year is invalid.'),
            ]);
        }
    }

    public function updateApplication(AdmissionApplication $application, array $data): AdmissionApplication
    {
        $guardians = $data['guardians'] ?? null;
        unset($data['guardians']);

        $this->validatePlacement(
            $data['grade_level_id'] ?? $application->grade_level_id,
            $data['intake_academic_year_id'] ?? $application->intake_academic_year_id,
        );

        $application->update($data);

        if ($guardians !== null) {
            $this->syncGuardians($application, $guardians);
        }

        return $application->fresh(['gradeLevel', 'intakeYear', 'primaryGuardian']);
    }

    public function submit(AdmissionApplication $application): void
    {
        $this->guardTransition($application, [AdmissionStatus::Inquiry->value, AdmissionStatus::Draft->value]);

        $application->update([
            'status' => AdmissionStatus::Submitted->value,
            'applied_at' => $application->applied_at ?? now(),
        ]);

        $this->notify($application, [RoleName::Registrar->value],
            __('Application submitted'),
            __('Application :number for :name is ready for review.', [
                'number' => $application->application_number,
                'name' => $application->full_name,
            ]));
    }

    public function review(AdmissionApplication $application): void
    {
        $this->guardTransition($application, [AdmissionStatus::Submitted->value]);

        $application->update(['status' => AdmissionStatus::UnderReview->value]);

        $this->notify($application, [RoleName::Registrar->value],
            __('Application under review'),
            __('Review started for :name (:number).', [
                'name' => $application->full_name,
                'number' => $application->application_number,
            ]));
    }

    public function scheduleAssessment(AdmissionApplication $application, array $data): AdmissionAssessment
    {
        $this->guardTransition($application, [
            AdmissionStatus::Submitted->value,
            AdmissionStatus::UnderReview->value,
        ]);

        $assessment = AdmissionAssessment::create([
            'application_id' => $application->getKey(),
            'type' => $data['type'],
            'scheduled_at' => $data['scheduled_at'],
            'location' => $data['location'] ?? null,
            'status' => AdmissionAssessmentStatus::Scheduled->value,
            'created_by' => auth()->id(),
        ]);

        $application->update(['status' => AdmissionStatus::AssessmentScheduled->value]);

        $this->notify($application, [RoleName::Registrar->value, RoleName::Principal->value],
            __('Admission assessment scheduled'),
            __('A :type assessment for :name is scheduled (:date).', [
                'type' => $assessment->typeLabel(),
                'name' => $application->full_name,
                'date' => $assessment->scheduled_at?->format('M j, Y g:i A'),
            ]));

        return $assessment;
    }

    public function recordAssessment(AdmissionAssessment $assessment, array $data): AdmissionAssessment
    {
        $assessment->update([
            'status' => $data['status'],
            'score' => $data['score'] ?? null,
            'notes' => $data['notes'] ?? null,
            'conducted_at' => in_array($data['status'], [AdmissionAssessmentStatus::Completed->value, AdmissionAssessmentStatus::NoShow->value], true) ? now() : null,
            'conducted_by' => auth()->id(),
        ]);

        $application = $assessment->application;

        if ($application->status === AdmissionStatus::AssessmentScheduled->value
            && $assessment->wasChanged('status')) {
            $application->update(['status' => AdmissionStatus::UnderReview->value]);
        }

        $this->notify($assessment->application, [RoleName::Registrar->value],
            __('Assessment recorded'),
            __(':type assessment for :name is now :status.', [
                'type' => $assessment->typeLabel(),
                'name' => $application->full_name,
                'status' => $assessment->statusLabel(),
            ]));

        return $assessment;
    }

    public function submitForApproval(AdmissionApplication $application, ?string $comment = null): void
    {
        $this->guardTransition($application, [
            AdmissionStatus::Submitted->value,
            AdmissionStatus::UnderReview->value,
            AdmissionStatus::AssessmentScheduled->value,
        ]);

        $application->update([
            'status' => AdmissionStatus::PendingApproval->value,
            'decision_comment' => $comment ?: $application->decision_comment,
        ]);

        $this->notify($application, [
            RoleName::VicePrincipal->value,
            RoleName::Principal->value,
            RoleName::SchoolAdmin->value,
        ],
            __('Admission pending approval'),
            __('Application :number for :name awaits a decision.', [
                'number' => $application->application_number,
                'name' => $application->full_name,
            ]));
    }

    public function decide(AdmissionApplication $application, string $decision, ?string $comment = null, bool $force = false): string
    {
        $this->guardTransition($application, [
            AdmissionStatus::Submitted->value,
            AdmissionStatus::PendingApproval->value,
            AdmissionStatus::UnderReview->value,
        ]);

        if ($decision === 'rejected') {
            return $this->reject($application, $comment);
        }

        if ($decision === 'waitlisted') {
            return $this->waitlist($application, $comment);
        }

        if ($decision !== 'approved') {
            throw ValidationException::withMessages([
                'decision' => __('The selected admission decision is invalid.'),
            ]);
        }

        if ($this->isGradeFull($application) && ! $force && ! $this->allowsOverride($application)) {
            return $this->waitlist($application, $comment ?? __('Grade capacity reached.'));
        }

        $application->update([
            'status' => AdmissionStatus::Approved->value,
            'decision' => 'approved',
            'decision_comment' => $comment ?: $application->decision_comment,
            'decided_at' => now(),
            'decision_by' => auth()->id(),
            'waitlist_position' => null,
        ]);

        $this->notify($application, [RoleName::Registrar->value, RoleName::Reception->value],
            __('Application approved'),
            __(':name was approved for grade :grade.', [
                'name' => $application->full_name,
                'grade' => $application->gradeLevel?->name ?? '—',
            ]));

        return AdmissionStatus::Approved->value;
    }

    public function reject(AdmissionApplication $application, ?string $comment = null): string
    {
        $this->guardTransition($application, [
            AdmissionStatus::Submitted->value,
            AdmissionStatus::PendingApproval->value,
            AdmissionStatus::UnderReview->value,
            AdmissionStatus::Waitlisted->value,
        ]);

        $application->update([
            'status' => AdmissionStatus::Rejected->value,
            'decision' => 'rejected',
            'decision_comment' => $comment,
            'decided_at' => now(),
            'decision_by' => auth()->id(),
            'waitlist_position' => null,
        ]);

        $this->notify($application, [RoleName::Registrar->value, RoleName::Reception->value],
            __('Application rejected'),
            __('Admission for :name was not approved.', ['name' => $application->full_name]));

        return AdmissionStatus::Rejected->value;
    }

    public function waitlist(AdmissionApplication $application, ?string $comment = null): string
    {
        $this->guardTransition($application, [
            AdmissionStatus::Submitted->value,
            AdmissionStatus::PendingApproval->value,
            AdmissionStatus::UnderReview->value,
            AdmissionStatus::Approved->value,
        ]);

        $position = (AdmissionApplication::waitlisted()
            ->where('grade_level_id', $application->grade_level_id)
            ->where('intake_academic_year_id', $application->intake_academic_year_id)
            ->max('waitlist_position') ?? 0) + 1;

        $application->update([
            'status' => AdmissionStatus::Waitlisted->value,
            'decision' => 'waitlisted',
            'decision_comment' => $comment ?: $application->decision_comment,
            'decided_at' => now(),
            'decision_by' => auth()->id(),
            'waitlisted_at' => now(),
            'waitlist_position' => $position,
        ]);

        $this->notify($application, [RoleName::Registrar->value, RoleName::Reception->value],
            __('Application waitlisted'),
            __(':name joined the waiting list for grade :grade at position #:position.', [
                'name' => $application->full_name,
                'grade' => $application->gradeLevel?->name ?? '—',
                'position' => $position,
            ]));

        return AdmissionStatus::Waitlisted->value;
    }

    public function promote(AdmissionApplication $application): void
    {
        $this->guardTransition($application, [AdmissionStatus::Waitlisted->value]);

        if ($this->isGradeFull($application)) {
            throw ValidationException::withMessages([
                'status' => __('The selected grade is still at capacity. Free a seat before promoting this applicant.'),
            ]);
        }

        $application->update([
            'status' => AdmissionStatus::Approved->value,
            'decision' => 'approved',
            'waitlist_position' => null,
        ]);

        $this->notify($application, [RoleName::Registrar->value],
            __('Promoted from waiting list'),
            __(':name was promoted for enrollment in grade :grade.', [
                'name' => $application->full_name,
                'grade' => $application->gradeLevel?->name ?? '—',
            ]));
    }

    public function withdraw(AdmissionApplication $application): void
    {
        $this->guardTransition($application, array_diff(AdmissionStatus::candidateStages(), [AdmissionStatus::Enrolled->value]));

        $application->update(['status' => AdmissionStatus::Withdrawn->value]);

        $this->notify($application, [RoleName::Registrar->value],
            __('Application withdrawn'),
            __('Application :number for :name was withdrawn.', [
                'number' => $application->application_number,
                'name' => $application->full_name,
            ]));
    }

    /* ------------------------------- Enrollment ------------------------------- */

    public function enroll(AdmissionApplication $application): Student
    {
        [$student, $application, $parent, $grade] = DB::transaction(function () use ($application) {
            $application = AdmissionApplication::query()
                ->with(['intakeYear', 'gradeLevel', 'guardians'])
                ->lockForUpdate()
                ->findOrFail($application->getKey());

            $this->guardTransition($application, [AdmissionStatus::Approved->value]);

            $year = $application->intakeYear ?? AcademicYear::current()->first();
            $grade = $application->gradeLevel;

            $classRoom = ClassRoom::where('grade_level_id', $grade?->getKey())
                ->where('academic_year_id', $year?->getKey())
                ->orderBy('name')
                ->lockForUpdate()
                ->first();

            $parent = $this->createParentUser($application);
            $roll = $year && $classRoom ? $this->students->nextRollNumber($classRoom, $year) : null;

            $studentNumber = $year && $classRoom
                ? $this->students->buildStudentNumber($year, $classRoom, $roll)
                : $this->fallbackStudentNumber($year);

            $student = Student::create([
                'student_number' => $studentNumber,
                'first_name' => $application->first_name,
                'last_name' => $application->last_name,
                'other_names' => $application->other_names,
                'gender' => $application->gender,
                'date_of_birth' => $application->date_of_birth,
                'national_id' => $application->national_id,
                'address' => $application->address,
                'previous_school' => $application->previous_school,
                'status' => StudentStatus::Active->value,
                'enrollment_date' => now()->toDateString(),
                'grade_level_id' => $grade?->getKey(),
                'class_room_id' => $classRoom?->getKey(),
                'academic_year_id' => $year?->getKey(),
                'user_id' => $parent?->getKey(),
            ]);

            if ($year && $grade && $classRoom) {
                StudentEnrollment::create([
                    'student_id' => $student->getKey(),
                    'academic_year_id' => $year->getKey(),
                    'grade_level_id' => $grade->getKey(),
                    'class_room_id' => $classRoom->getKey(),
                    'roll_number' => $roll,
                    'status' => 'active',
                    'enrolled_at' => now()->toDateString(),
                ]);
            } elseif ($year && $grade) {
                StudentEnrollment::create([
                    'student_id' => $student->getKey(),
                    'academic_year_id' => $year->getKey(),
                    'grade_level_id' => $grade->getKey(),
                    'status' => 'active',
                    'enrolled_at' => now()->toDateString(),
                ]);
            }

            $this->linkGuardians($application, $student, $parent);

            $application->update([
                'status' => AdmissionStatus::Enrolled->value,
                'student_id' => $student->getKey(),
                'parent_user_id' => $parent?->getKey(),
                'enrolled_at' => now(),
            ]);

            return [$student, $application, $parent, $grade];
        });

        $this->notifications->sendToRoles(
            [RoleName::Registrar->value, RoleName::SchoolAdmin->value],
            $this->payload($application, 'admission', 'enrollment',
                __('New enrolment'),
                __(':name enrolled as :number in grade :grade.', [
                    'name' => $application->full_name,
                    'number' => $student->student_number,
                    'grade' => $grade?->name ?? '—',
                ]))
        );

        if ($parent) {
            $this->notifications->sendToUser($parent->getKey(), [
                'type' => 'enrollment',
                'category' => 'admissions',
                'priority' => 'high',
                'icon' => 'graduation',
                'title' => __('Enrollment confirmed'),
                'body' => __(':name is now enrolled at our school.', ['name' => $application->full_name]),
                'redirect_url' => route('cms.parent.dashboard'),
            ]);
        }

        return $student->fresh(['gradeLevel', 'classRoom', 'academicYear']);
    }
    private function createParentUser(AdmissionApplication $application): ?User
    {
        $guardian = $application->primaryGuardian;

        if (! $guardian || ! $guardian->email) {
            return null;
        }

        $user = User::query()->where('email', $guardian->email)->first();

        if (! $user) {
            $user = User::create([
                'first_name' => $guardian->first_name,
                'last_name' => $guardian->last_name,
                'email' => $guardian->email,
                'password' => Str::password(16),
                'status' => 'active',
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]);
        }

        $role = Role::where('name', RoleName::Parent->value)->first();
        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->getKey()]);
        }

        return $user;
    }

    private function linkGuardians(AdmissionApplication $application, Student $student, ?User $parentUser): void
    {
        foreach ($application->guardians as $index => $guardian) {
            $record = Guardian::create([
                'user_id' => $index === 0 ? $parentUser?->getKey() : null,
                'first_name' => $guardian->first_name,
                'last_name' => $guardian->last_name,
                'relationship' => $guardian->relationship,
                'phone' => $guardian->phone,
                'email' => $guardian->email,
                'occupation' => $guardian->occupation,
                'national_id' => $guardian->national_id,
                'address' => $guardian->address,
            ]);

            $student->guardians()->attach($record->getKey(), ['is_primary' => $guardian->is_primary || $index === 0]);

            if ($index === 0) {
                $student->update(['guardian_id' => $record->getKey()]);
            }
        }
    }

    /* -------------------------------- Capacity -------------------------------- */

    public function capacityFor(GradeLevel $grade, ?AcademicYear $year = null): int
    {
        $year ??= AcademicYear::current()->first();

        $capacity = $year
            ? GradeCapacity::where('grade_level_id', $grade->getKey())->where('academic_year_id', $year->getKey())->value('capacity')
            : null;

        if ($capacity !== null) {
            return (int) $capacity;
        }

        return (int) ClassRoom::where('grade_level_id', $grade->getKey())
            ->where('academic_year_id', $year?->getKey())
            ->sum('capacity') ?: 40;
    }

    public function seatsTaken(GradeLevel $grade, ?AcademicYear $year = null, ?string $excludeApplicationId = null): int
    {
        $year ??= AcademicYear::current()->first();

        $students = Student::where('grade_level_id', $grade->getKey())
            ->whereHas('enrollments', fn ($query) => $query
                ->where('academic_year_id', $year?->getKey())
                ->where('status', 'active'))
            ->count();

        $approvedPending = AdmissionApplication::where('grade_level_id', $grade->getKey())
            ->where('intake_academic_year_id', $year?->getKey())
            ->where('status', AdmissionStatus::Approved->value)
            ->when($excludeApplicationId, fn ($query) => $query->where(
                $query->getModel()->getKeyName(),
                '!=',
                $excludeApplicationId
            ))
            ->count();

        return $students + $approvedPending;
    }

    public function isGradeFull(AdmissionApplication $application): bool
    {
        $grade = $application->gradeLevel;

        if (! $grade) {
            return false;
        }

        return $this->seatsTaken($grade, $application->intakeYear, $application->getKey()) >= $this->capacityFor($grade, $application->intakeYear);
    }

    private function allowsOverride(AdmissionApplication $application): bool
    {
        $capacity = $application->intakeYear
            ? GradeCapacity::where('grade_level_id', $application->grade_level_id)
                ->where('academic_year_id', $application->intake_academic_year_id)
                ->first()
            : null;

        return (bool) ($capacity?->allow_override ?? false);
    }

    /* ---------------------------------- Helpers --------------------------------- */

    private function currentYearId(): ?string
    {
        return AcademicYear::current()->first()?->getKey() ?? AcademicYear::latest('start_date')->first()?->getKey();
    }

    private function guardTransition(AdmissionApplication $application, array $allowed): void
    {
        if (! in_array($application->status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => __('Admission applications in the ":status" state cannot perform this action.', [
                    'status' => $application->statusLabel(),
                ]),
            ]);
        }
    }

    private function notify(AdmissionApplication $application, array $roles, string $title, string $body): void
    {
        $this->notifications->sendToRoles($roles, $this->payload($application, 'admission', 'application', $title, $body));
    }

    private function payload(AdmissionApplication $application, string $type, string $category, string $title, string $body): array
    {
        return [
            'type' => $type,
            'category' => $category,
            'priority' => 'high',
            'icon' => 'clipboard',
            'title' => $title,
            'body' => $body,
            'redirect_url' => route('admissions.applications.show', $application),
        ];
    }

    private function syncGuardians(AdmissionApplication $application, array $guardians): void
    {
        if (empty($guardians)) {
            return;
        }

        $keep = [];

        foreach ($guardians as $entry) {
            $id = $entry['id'] ?? null;

            if ($id && $application->guardians()->whereKey($id)->exists()) {
                $application->guardians()->whereKey($id)->update($this->guardianFields($entry));
                $keep[] = $id;

                continue;
            }

            unset($entry['id']);
            $keep[] = $application->guardians()->create($this->guardianFields($entry))->getKey();
        }

        $application->guardians()->whereNotIn('id', $keep)->delete();
    }

    private function guardianFields(array $entry): array
    {
        return [
            'first_name' => $entry['first_name'] ?? null,
            'last_name' => $entry['last_name'] ?? null,
            'relationship' => $entry['relationship'] ?? 'guardian',
            'phone' => $entry['phone'] ?? null,
            'email' => $entry['email'] ?? null,
            'occupation' => $entry['occupation'] ?? null,
            'national_id' => $entry['national_id'] ?? null,
            'address' => $entry['address'] ?? null,
            'is_primary' => (bool) ($entry['is_primary'] ?? false),
            'is_emergency' => (bool) ($entry['is_emergency'] ?? false),
        ];
    }
}

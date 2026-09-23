<?php

namespace App\Domains\Students\Services;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Students\Models\EmergencyContact;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use App\Domains\Students\Models\StudentDocument;
use App\Domains\Students\Models\StudentEnrollment;
use App\Domains\Students\Models\StudentStatusHistory;
use App\Domains\Students\Models\StudentTimeline;
use App\Domains\Students\Models\StudentTransfer;
use App\Support\Enums\StudentStatus;
use App\Support\Enums\StudentTimelineType;
use App\Support\Enums\StudentTransferType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Student::withTrashed()
            ->with(['gradeLevel', 'classRoom', 'primaryGuardian'])
            ->search($filters['q'] ?? null)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['gender'] ?? null, fn ($query, $gender) => $query->where('gender', $gender))
            ->when($filters['grade_level_id'] ?? null, fn ($query, $grade) => $query->where('grade_level_id', $grade))
            ->when($filters['class_room_id'] ?? null, fn ($query, $class) => $query->where('class_room_id', $class))
            ->when($filters['academic_year_id'] ?? null, fn ($query, $year) => $query->where('academic_year_id', $year))
            ->when(($filters['transfer'] ?? null) === '1', fn ($query) => $query->whereIn('status', [
                StudentStatus::Transferred->value,
                StudentStatus::Withdrawn->value,
            ]))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function currentYear(): AcademicYear
    {
        return AcademicYear::current()->firstOrFail();
    }

    /**
     * Roll numbers are unique within a section (class room + academic year),
     * not globally. The next roll is the highest existing roll plus one.
     */
    public function nextRollNumber(ClassRoom $classRoom, AcademicYear $year): int
    {
        $lastRoll = StudentEnrollment::rollAssignable($classRoom->getKey(), $year->getKey())
            ->value('roll_number');

        if ($lastRoll === null) {
            $existing = StudentEnrollment::where('class_room_id', $classRoom->getKey())
                ->where('academic_year_id', $year->getKey())
                ->count();

            return $existing + 1;
        }

        return (int) $lastRoll + 1;
    }

    /**
     * Permanent student ID built from: BMA + year + grade + section + roll.
     * Example: BMA265A001 (Grade 5, Section A, roll 1 in academic year 2026/27).
     */
    public function buildStudentNumber(AcademicYear $year, ClassRoom $classRoom, int $roll): string
    {
        $gradeCode = $classRoom->gradeLevel->code;
        $section = strtoupper(trim((string) str($classRoom->name)->afterLast(' ')));

        return 'BMA'.$year->start_date->format('y').$gradeCode.$section.str_pad((string) $roll, 3, '0', STR_PAD_LEFT);
    }

    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            $class = ClassRoom::with('gradeLevel')
                ->lockForUpdate()
                ->findOrFail($data['class_room_id']);

            $year = $this->currentYear();
            $roll = $this->nextRollNumber($class, $year);
            $guardianData = $data['guardian'] ?? null;
            unset($data['guardian']);

            $photo = $data['photo'] ?? null;
            unset($data['photo']);

            $student = Student::create([
                ...$data,
                'student_number' => $this->buildStudentNumber($year, $class, $roll),
                'grade_level_id' => $class->grade_level_id,
                'class_room_id' => $class->getKey(),
                'academic_year_id' => $year->getKey(),
                'status' => StudentStatus::New->value,
                'photo_path' => $photo ? $photo->store('student-photos', 'public') : null,
            ]);

            $this->createGuardian($guardianData, $student);
            $this->createEnrollment($student, $class, $data['enrollment_date'] ?? now()->toDateString(), $year, $roll);

            $this->logTimeline(
                $student,
                StudentTimelineType::Enrolled,
                sprintf('Student registered and placed in %s.', $class->name),
                ['class_room_id' => $class->getKey(), 'roll_number' => $roll]
            );

            return $student->load(['gradeLevel', 'classRoom', 'primaryGuardian', 'academicYear'])->refresh();
        });
    }

    public function update(Student $student, array $data): Student
    {
        $guardianData = $data['guardian'] ?? null;
        unset($data['guardian']);

        $photo = $data['photo'] ?? null;
        unset($data['photo']);

        if ($photo) {
            $data['photo_path'] = $photo->store('student-photos', 'public');
        }

        if (isset($data['class_room_id'])) {
            $class = ClassRoom::with('gradeLevel')->findOrFail($data['class_room_id']);
            $data['grade_level_id'] = $class->grade_level_id;
        }

        $student->update($data);

        if ($guardianData && array_filter($guardianData)) {
            $student->update(['guardian_id' => $this->updateOrCreateGuardian($student, $guardianData)]);
        }

        return $student->load(['gradeLevel', 'classRoom', 'primaryGuardian', 'academicYear']);
    }

    public function delete(Student $student): void
    {
        $student->delete();
    }

    /* ------------------------------ Promotion ------------------------------ */

    /**
     * Promote every active student in $sourceClass into the target academic
     * year. Grade 8 students graduate instead of receiving a new enrollment.
     *
     * @return array{promoted: int, graduated: int, skipped: int}
     */
    public function promote(ClassRoom $sourceClass, AcademicYear $targetYear, ?string $note = null, ?string $userId = null): array
    {
        [$promoted, $graduated, $skipped] = [0, 0, 0];

        foreach ($this->promotionCandidates($sourceClass) as $student) {
            $nextGrade = $this->nextGrade($student->gradeLevel);

            if ($nextGrade === null) {
                $this->graduate($student, $note, $userId);
                $graduated++;

                continue;
            }

            $targetClass = $this->targetClassFor($nextGrade, $targetYear, $sourceClass);

            if ($targetClass === null) {
                $skipped++;

                continue;
            }

            $currentEnrollment = $student->activeEnrollment();

            DB::transaction(function () use ($student, $currentEnrollment, $targetClass, $targetYear, $note, $userId) {
                $roll = $this->nextRollNumber($targetClass, $targetYear);

                if ($currentEnrollment) {
                    $currentEnrollment->update([
                        'status' => 'promoted',
                        'result' => 'promoted',
                        'left_at' => $targetYear->start_date->subDay(),
                    ]);
                }

                StudentEnrollment::create([
                    'student_id' => $student->getKey(),
                    'academic_year_id' => $targetYear->getKey(),
                    'grade_level_id' => $targetClass->grade_level_id,
                    'class_room_id' => $targetClass->getKey(),
                    'roll_number' => $roll,
                    'status' => 'active',
                    'enrolled_at' => $targetYear->start_date,
                    'notes' => $note ?: 'Promoted from '.($student->classRoom?->name ?? 'previous grade'),
                ]);

                $student->update([
                    'grade_level_id' => $targetClass->grade_level_id,
                    'class_room_id' => $targetClass->getKey(),
                    'academic_year_id' => $targetYear->getKey(),
                    'status' => StudentStatus::Active->value,
                ]);

                $this->recordStatusChange($student, StudentStatus::Active, StudentStatus::Active, $note ?: 'Annual promotion', $userId);
                $this->logTimeline(
                    $student,
                    StudentTimelineType::Promoted,
                    sprintf('Promoted to %s %s.', $targetClass->gradeLevel->name, $targetClass->name),
                    ['class_room_id' => $targetClass->getKey(), 'roll_number' => $roll, 'academic_year_id' => $targetYear->getKey()],
                    $userId
                );
            });

            $promoted++;
        }

        return ['promoted' => $promoted, 'graduated' => $graduated, 'skipped' => $skipped];
    }

    public function promotionCandidates(ClassRoom $sourceClass): Collection
    {
        $yearId = $sourceClass->academic_year_id;

        return Student::query()
            ->whereIn('status', [StudentStatus::Active->value, StudentStatus::New->value])
            ->where('class_room_id', $sourceClass->getKey())
            ->where('academic_year_id', $yearId)
            ->with(['gradeLevel', 'classRoom'])
            ->get();
    }

    private function nextGrade(?GradeLevel $current): ?GradeLevel
    {
        if (! $current) {
            return null;
        }

        return GradeLevel::ordered()
            ->where('sort_order', '>', $current->sort_order)
            ->first();
    }

    private function targetClassFor(GradeLevel $grade, AcademicYear $year, ClassRoom $sourceClass): ?ClassRoom
    {
        $section = strtoupper(trim((string) str($sourceClass->name)->afterLast(' ')));

        $match = ClassRoom::where('grade_level_id', $grade->getKey())
            ->where('academic_year_id', $year->getKey())
            ->whereRaw('UPPER(name) LIKE ?', ['% '.$section])
            ->first();

        return $match ?? ClassRoom::where('grade_level_id', $grade->getKey())
            ->where('academic_year_id', $year->getKey())
            ->orderBy('name')
            ->first();
    }

    private function graduate(Student $student, ?string $note, ?string $userId): void
    {
        DB::transaction(function () use ($student, $note, $userId) {
            $currentEnrollment = $student->activeEnrollment();

            if ($currentEnrollment) {
                $currentEnrollment->update([
                    'status' => 'graduated',
                    'result' => 'graduated',
                    'left_at' => now()->toDateString(),
                ]);
            }

            $student->update(['status' => StudentStatus::Graduated->value]);
            $this->recordStatusChange($student, StudentStatus::Active, StudentStatus::Graduated, $note ?: 'Completed Grade 8', $userId);
            $this->logTimeline($student, StudentTimelineType::Promoted, 'Graduated from Grade 8.', [], $userId);
        });
    }

    /* ------------------------------ Transfers ------------------------------ */

    public function transferInternal(Student $student, ClassRoom $toClass, ?string $transferDate = null, ?string $reason = null, ?string $userId = null): StudentTransfer
    {
        $date = $transferDate ?: now()->toDateString();

        return DB::transaction(function () use ($student, $toClass, $date, $reason, $userId) {
            $current = $student->activeEnrollment();
            $fromClass = $current?->classRoom;
            $year = $toClass->academicYear;

            $roll = $this->nextRollNumber($toClass, $year);

            if ($current) {
                $current->update([
                    'class_room_id' => $toClass->getKey(),
                    'roll_number' => $roll,
                    'notes' => $reason ?: 'Section changed from '.($fromClass?->name ?? 'previous').' to '.$toClass->name,
                ]);
            }

            $student->update([
                'grade_level_id' => $toClass->grade_level_id,
                'class_room_id' => $toClass->getKey(),
            ]);

            $transfer = StudentTransfer::create([
                'student_id' => $student->getKey(),
                'type' => StudentTransferType::Internal->value,
                'from_class_room_id' => $fromClass?->getKey(),
                'to_class_room_id' => $toClass->getKey(),
                'transfer_date' => $date,
                'reason' => $reason,
                'approved_by_id' => $userId,
            ]);

            $this->logTimeline(
                $student,
                StudentTimelineType::SectionChange,
                sprintf('Section changed from %s to %s.', $fromClass?->name ?? 'previous class', $toClass->name),
                ['from_class_room_id' => $fromClass?->getKey(), 'to_class_room_id' => $toClass->getKey(), 'roll_number' => $roll],
                $userId
            );

            return $transfer;
        });
    }

    public function transferExternal(Student $student, array $data, ?string $userId = null): StudentTransfer
    {
        return DB::transaction(function () use ($student, $data, $userId) {
            $date = $data['transfer_date'] ?: now()->toDateString();
            $current = $student->activeEnrollment();
            $fromClass = $current?->classRoom;

            if ($current) {
                $current->update([
                    'status' => 'left',
                    'left_at' => $date,
                ]);
            }

            $transfer = StudentTransfer::create([
                'student_id' => $student->getKey(),
                'type' => StudentTransferType::External->value,
                'from_class_room_id' => $fromClass?->getKey(),
                'to_class_room_id' => null,
                'transfer_date' => $date,
                'destination_school' => $data['destination_school'] ?? null,
                'reason' => $data['reason'] ?? null,
                'certificate_number' => $data['certificate_number'] ?? null,
                'approved_by_id' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);

            $fromStatus = StudentStatus::tryFrom($student->status) ?? StudentStatus::Active;
            $student->update(['status' => StudentStatus::Transferred->value]);
            $this->recordStatusChange($student, $fromStatus, StudentStatus::Transferred, $data['reason'] ?? 'External transfer', $userId);
            $this->logTimeline($student, StudentTimelineType::Transfer, 'Transferred out to '.($data['destination_school'] ?? 'another school').'.', $data, $userId);

            return $transfer;
        });
    }

    /* ------------------------------ Records ------------------------------ */

    public function recordStatusChange(Student $student, ?StudentStatus $from, StudentStatus $to, ?string $reason = null, ?string $userId = null): void
    {
        StudentStatusHistory::create([
            'student_id' => $student->getKey(),
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'reason' => $reason,
            'acted_by_id' => $userId,
        ]);
    }

    public function logTimeline(Student $student, StudentTimelineType $type, string $description, array $meta = [], ?string $userId = null): void
    {
        StudentTimeline::create([
            'student_id' => $student->getKey(),
            'type' => $type->value,
            'description' => $description,
            'event_date' => now()->toDateString(),
            'meta' => $meta ?: null,
            'created_by_id' => $userId,
        ]);
    }

    public function saveMedicalRecord(Student $student, array $data, ?string $userId = null): void
    {
        $student->medicalRecord()->updateOrCreate(
            ['student_id' => $student->getKey()],
            [...$data, 'updated_by_id' => $userId]
        );

        $this->logTimeline($student, StudentTimelineType::MedicalUpdated, 'Medical record updated.', [], $userId);
    }

    public function addEmergencyContact(Student $student, array $data, ?string $userId = null): EmergencyContact
    {
        return $student->emergencyContacts()->create([...$data, 'created_by_id' => $userId]);
    }

    public function addDocument(Student $student, StudentDocument $document, ?string $userId = null): StudentDocument
    {
        $this->logTimeline($student, StudentTimelineType::DocumentUploaded, 'Document uploaded: '.$document->name.'.', ['category' => $document->category], $userId);

        return $document;
    }

    private function createGuardian(?array $data, Student $student): ?Guardian
    {
        if (! $data || ! array_filter($data)) {
            return null;
        }

        $guardian = Guardian::create($data);
        $guardian->students()->attach($student->getKey(), ['is_primary' => true]);
        $student->update(['guardian_id' => $guardian->getKey()]);

        return $guardian;
    }

    private function updateOrCreateGuardian(Student $student, array $data): string
    {
        $guardian = $student->primaryGuardian;

        if ($guardian) {
            $guardian->update($data);

            return $guardian->getKey();
        }

        $guardian = Guardian::create($data);
        $guardian->students()->attach($student->getKey(), ['is_primary' => true]);

        return $guardian->getKey();
    }

    private function createEnrollment(Student $student, ClassRoom $class, string $enrolledAt, AcademicYear $year, int $roll): void
    {
        StudentEnrollment::create([
            'student_id' => $student->getKey(),
            'academic_year_id' => $year->getKey(),
            'grade_level_id' => $class->grade_level_id,
            'class_room_id' => $class->getKey(),
            'roll_number' => $roll,
            'status' => 'active',
            'enrolled_at' => $enrolledAt,
        ]);
    }
}

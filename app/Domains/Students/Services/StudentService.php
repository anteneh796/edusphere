<?php

namespace App\Domains\Students\Services;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use App\Domains\Students\Models\StudentEnrollment;
use App\Support\Enums\StudentStatus;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Student::query()
            ->with(['gradeLevel', 'classRoom', 'primaryGuardian'])
            ->search($filters['q'] ?? null)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['grade_level_id'] ?? null, fn ($query, $grade) => $query->where('grade_level_id', $grade))
            ->when($filters['class_room_id'] ?? null, fn ($query, $class) => $query->where('class_room_id', $class))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function currentYear(): AcademicYear
    {
        return AcademicYear::current()->firstOrFail();
    }

    public function generateStudentNumber(): string
    {
        $year = now()->format('y');
        $prefix = "ES-{$year}-";

        $last = Student::withTrashed()
            ->where('student_number', 'like', "{$prefix}%")
            ->orderByDesc('student_number')
            ->value('student_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $data): Student
    {
        $class = ClassRoom::with('gradeLevel')->findOrFail($data['class_room_id']);
        $guardianData = $data['guardian'] ?? null;
        unset($data['guardian']);

        $student = Student::create([
            ...$data,
            'student_number' => $this->generateStudentNumber(),
            'grade_level_id' => $class->grade_level_id,
            'class_room_id' => $class->getKey(),
            'academic_year_id' => $this->currentYear()->getKey(),
            'status' => StudentStatus::New->value,
        ]);

        $guardian = $this->createGuardian($guardianData, $student);

        $this->createEnrollment($student, $class, $data['enrollment_date'] ?? now()->toDateString());

        return $student->load(['gradeLevel', 'classRoom', 'primaryGuardian', 'academicYear'])->refresh();
    }

    public function update(Student $student, array $data): Student
    {
        $guardianData = $data['guardian'] ?? null;
        unset($data['guardian']);

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

    private function createEnrollment(Student $student, ClassRoom $class, string $enrolledAt): void
    {
        StudentEnrollment::create([
            'student_id' => $student->getKey(),
            'academic_year_id' => $this->currentYear()->getKey(),
            'grade_level_id' => $class->grade_level_id,
            'class_room_id' => $class->getKey(),
            'status' => 'active',
            'enrolled_at' => $enrolledAt,
        ]);
    }
}

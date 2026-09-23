<?php

namespace Database\Seeders;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Services\AttendanceService;
use App\Domains\Students\Models\Student;
use App\Support\Enums\AttendanceSessionStatus;
use App\Support\Enums\AttendanceStatus;
use App\Support\Enums\RoleName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds believable attendance history for the current year so the
 * attendance dashboard, reports, alerts, and correction queues have
 * data for demo accounts. Idempotent and deterministic: sessions are
 * keyed on (class_room_id, date) and statuses derive from a stable hash.
 */
class DemoAttendanceSeeder extends Seeder
{
    private const HISTORY_DAYS = 15;

    public function run(): void
    {
        $year = $this->demoYear();

        if (! $year) {
            return;
        }

        $classes = ClassRoom::with('students')
            ->where('academic_year_id', $year->getKey())
            ->get()
            ->filter(fn (ClassRoom $class) => $class->students->isNotEmpty());

        if ($classes->isEmpty()) {
            return;
        }

        // The class with the most students keeps today's session open so a
        // teacher/demo can still act on it; the rest stay submitted + locked.
        $largestClass = $classes->sortByDesc(fn (ClassRoom $class) => $class->students->count())->first();

        foreach ($classes as $class) {
            $this->seedHistory($class);

            if ($class->getKey() === $largestClass?->getKey()) {
                $this->seedTodayOpen($class);
            } else {
                $this->seedTodayClosed($class);
            }

            $this->seedPendingCorrection($class);
        }
    }

    private function seedHistory(ClassRoom $class): void
    {
        $teacher = $this->classTeacher($class);

        foreach ($this->recentSchoolDates(self::HISTORY_DAYS) as $date) {
            $session = $this->updateSession($class, $date, $teacher, open: false);

            foreach ($class->students as $index => $student) {
                $status = $this->statusFor($class, $date, $index);

                $markedAt = $date->copy()->setTime(8, 15, random_int(0, 20));

                $record = AttendanceRecord::updateOrCreate(
                    ['attendance_session_id' => $session->getKey(), 'student_id' => $student->getKey()],
                    [
                        'status' => $status,
                        'marked_by_id' => $teacher?->getKey(),
                        'note' => null,
                    ]
                );

                $record->forceFill(['created_at' => $markedAt, 'updated_at' => $markedAt])->saveQuietly();
            }
        }
    }

    private function seedTodayClosed(ClassRoom $class): void
    {
        $teacher = $this->classTeacher($class);
        $date = Carbon::today();

        $model = $this->updateSession($class, $date, $teacher, open: false);

        foreach ($class->students as $index => $student) {
            AttendanceRecord::updateOrCreate(
                ['attendance_session_id' => $model->getKey(), 'student_id' => $student->getKey()],
                [
                    'status' => $this->statusFor($class, $date, $index),
                    'marked_by_id' => $teacher?->getKey(),
                    'note' => null,
                ]
            );
        }
    }

    private function seedTodayOpen(ClassRoom $class): void
    {
        $teacher = $this->classTeacher($class);
        $date = Carbon::today();

        $session = AttendanceSession::updateOrCreate(
            ['class_room_id' => $class->getKey(), 'date' => $date],
            [
                'academic_year_id' => $class->academic_year_id,
                'taken_by_id' => $teacher?->getKey(),
                'status' => AttendanceSessionStatus::Open->value,
                'opened_at' => now(),
                'closed_at' => null,
                'submitted_at' => null,
                'locked_at' => null,
            ]
        );

        foreach ($class->students as $index => $student) {
            AttendanceRecord::updateOrCreate(
                ['attendance_session_id' => $session->getKey(), 'student_id' => $student->getKey()],
                [
                    'status' => $index === 0 ? AttendanceStatus::Present->value : $this->statusFor($class, $date, $index),
                    'marked_by_id' => $teacher?->getKey(),
                    'note' => null,
                ]
            );
        }
    }

    private function updateSession(ClassRoom $class, Carbon $date, ?User $teacher, bool $open): AttendanceSession
    {
        if ($open) {
            return AttendanceSession::updateOrCreate(
                ['class_room_id' => $class->getKey(), 'date' => $date],
                [
                    'academic_year_id' => $class->academic_year_id,
                    'taken_by_id' => $teacher?->getKey(),
                    'status' => AttendanceSessionStatus::Open->value,
                    'opened_at' => $date->copy()->setTime(8, 15),
                    'closed_at' => null,
                    'submitted_at' => null,
                    'locked_at' => null,
                ]
            );
        }

        $closedAt = $date->copy()->setTime(8, 55);

        return AttendanceSession::updateOrCreate(
            ['class_room_id' => $class->getKey(), 'date' => $date],
            [
                'academic_year_id' => $class->academic_year_id,
                'taken_by_id' => $teacher?->getKey(),
                'status' => AttendanceSessionStatus::Closed->value,
                'opened_at' => $date->copy()->setTime(8, 15),
                'closed_at' => $closedAt,
                'submitted_at' => $closedAt,
                'locked_at' => $closedAt,
            ]
        );
    }

    private function seedPendingCorrection(ClassRoom $class): void
    {
        $session = AttendanceSession::query()
            ->where('class_room_id', $class->getKey())
            ->where('status', AttendanceSessionStatus::Closed->value)
            ->whereHas('records', fn ($query) => $query->where('status', AttendanceStatus::Absent->value))
            ->latest('date')
            ->first();

        if (! $session) {
            return;
        }

        // The most-absent student in this class has a pending "was present" claim.
        $record = $session->records()
            ->where('status', AttendanceStatus::Absent->value)
            ->with('student')
            ->first();

        if (! $record) {
            return;
        }

        if ($record->corrections()->where('status', 'pending')->where('requested_status', AttendanceStatus::Present->value)->exists()) {
            return;
        }

        $service = new AttendanceService;

        $service->requestCorrection(
            $record,
            AttendanceStatus::Present->value,
            'Returned with a note from home; was marked absent in error.',
            $session->taken_by_id ?? User::query()->whereHas('roles', fn ($query) => $query->where('name', RoleName::Teacher->value))->value('id')
        );
    }

    /**
     * The year to seed into: the flagged current year when it has classes
     * with students, otherwise the most recent year that does (mirrors
     * DemoParentSeeder's fallback for stale current-year flags).
     */
    private function demoYear(): ?AcademicYear
    {
        $current = AcademicYear::where('is_current', true)->first();

        if ($current && $current->classRooms()->whereHas('students')->exists()) {
            return $current;
        }

        return AcademicYear::query()
            ->whereHas('classRooms', fn ($query) => $query->whereHas('students'))
            ->latest('start_date')
            ->first();
    }

    private function classTeacher(ClassRoom $class): ?User
    {
        $assignment = ClassSubject::query()
            ->where('class_room_id', $class->getKey())
            ->orderByDesc('is_homeroom')
            ->orderBy('position')
            ->first();

        if ($assignment && $assignment->teacher_id) {
            return User::find($assignment->teacher_id);
        }

        $teacherId = ClassSubject::query()->where('class_room_id', $class->getKey())->value('teacher_id');

        return $teacherId ? User::find($teacherId) : null;
    }

    /**
     * The most recent school weekdays, oldest first, capped at HISTORY_DAYS.
     *
     * @return list<Carbon>
     */
    private function recentSchoolDates(int $limit): array
    {
        $dates = [];

        for ($i = $limit - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            if ($date->isWeekend()) {
                continue;
            }

            $dates[] = $date;
        }

        return $dates;
    }

    private function statusFor(ClassRoom $class, Carbon $date, int $studentIndex): string
    {
        $seed = crc32($class->getKey().'|'.$date->toDateString().'|'.$studentIndex) & 0xFFFFFFFF;
        $roll = $seed % 100;

        return match (true) {
            // First student leans absent, second leans late: forces alert.
            $studentIndex === 0 && $roll < 45 => AttendanceStatus::Absent->value,
            $studentIndex === 1 && $roll < 40 => AttendanceStatus::Late->value,
            $roll < 8 => AttendanceStatus::Absent->value,
            $roll < 15 => AttendanceStatus::Late->value,
            $roll < 20 => AttendanceStatus::Excused->value,
            default => AttendanceStatus::Present->value,
        };
    }
}

<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Students\Models\Student;
use App\Support\Enums\AttendanceSessionStatus;
use App\Support\Enums\AttendanceStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceService
{
    public function currentYear(): AcademicYear
    {
        return AcademicYear::current()->orderBy('start_date')->first()
            ?? throw new \RuntimeException('No current academic year is configured.');
    }

    public function openSession(ClassRoom $class, string|Carbon $date, string $takenById): AttendanceSession
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        $date = $date->startOfDay();

        return AttendanceSession::updateOrCreate(
            ['class_room_id' => $class->getKey(), 'date' => $date],
            [
                'academic_year_id' => $this->currentYear()->getKey(),
                'taken_by_id' => $takenById,
                'status' => AttendanceSessionStatus::Open->value,
                'opened_at' => now(),
                'closed_at' => null,
            ]
        );
    }

    public function upsertRecords(AttendanceSession $session, string $markedById, array $rows): void
    {
        foreach ($rows as $row) {
            $studentId = $row['student_id'] ?? null;

            if (! $studentId || empty($row['status'])) {
                continue;
            }

            AttendanceRecord::updateOrCreate(
                ['attendance_session_id' => $session->getKey(), 'student_id' => $studentId],
                [
                    'status' => $row['status'],
                    'note' => ! empty($row['note']) ? $row['note'] : null,
                    'marked_by_id' => $markedById,
                ]
            );
        }
    }

    public function closeSession(AttendanceSession $session): void
    {
        $session->update([
            'status' => AttendanceSessionStatus::Closed->value,
            'closed_at' => now(),
        ]);
    }

    /**
     * Merge offline-queued records into the matching session for each class/date.
     *
     * @param  array<int, array<string, mixed>>  $records
     * @return array{records: int, sessions: int}
     */
    public function mergeOfflineRecords(array $records): array
    {
        $grouped = collect($records)->groupBy(fn (array $row) => $row['class_room_id'].'|'.$row['date']);

        $synced = 0;
        $sessions = 0;

        foreach ($grouped as $key => $rows) {
            [$classRoomId, $date] = explode('|', $key);

            $class = ClassRoom::find($classRoomId);

            if (! $class) {
                continue;
            }

            $resolved = $rows->map(function (array $row) {
                $student = $this->resolveStudent($row);

                return [
                    'student_id' => $student?->getKey(),
                    'status' => $row['status'] ?? null,
                    'note' => $row['note'] ?? null,
                ];
            })->filter(fn (array $row) => $row['student_id'] !== null);

            if ($resolved->isEmpty()) {
                continue;
            }

            $session = $this->openSession($class, $date, auth()->id());
            $sessions++;

            $this->upsertRecords($session, auth()->id(), $resolved->all());

            $synced += $resolved->count();
        }

        return ['records' => $synced, 'sessions' => $sessions];
    }

    public function summary(Collection $records): array
    {
        $counts = [
            AttendanceStatus::Present->value => 0,
            AttendanceStatus::Absent->value => 0,
            AttendanceStatus::Late->value => 0,
            AttendanceStatus::Excused->value => 0,
        ];

        foreach ($records as $record) {
            $status = $record->status instanceof AttendanceStatus
                ? $record->status->value
                : $record->status;

            if (array_key_exists($status, $counts)) {
                $counts[$status]++;
            }
        }

        return $counts;
    }

    private function resolveStudent(array $row): ?Student
    {
        if (! empty($row['student_id'])) {
            return Student::find($row['student_id']);
        }

        if (! empty($row['student_number'])) {
            return Student::where('student_number', $row['student_number'])->first();
        }

        return null;
    }
}

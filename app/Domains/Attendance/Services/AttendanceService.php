<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Attendance\Models\AttendanceCorrection;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Settings\Models\Setting;
use App\Domains\Students\Models\Student;
use App\Support\Enums\AttendanceCorrectionStatus;
use App\Support\Enums\AttendanceSessionStatus;
use App\Support\Enums\AttendanceStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        $currentYear = $this->currentYear();

        if ((string) $class->academic_year_id !== (string) $currentYear->getKey()) {
            throw new \DomainException('Attendance can only be recorded for a class in the current academic year.');
        }

        if ($date->isFuture()) {
            throw new \DomainException('Attendance cannot be recorded for a future date.');
        }

        return DB::transaction(function () use ($class, $date, $takenById, $currentYear) {
            return AttendanceSession::query()
                ->lockForUpdate()
                ->firstOrCreate(
                    ['class_room_id' => $class->getKey(), 'date' => $date],
                    [
                        'academic_year_id' => $currentYear->getKey(),
                        'taken_by_id' => $takenById,
                        'status' => AttendanceSessionStatus::Open->value,
                        'opened_at' => now(),
                        'closed_at' => null,
                    ]
                );
        });
    }

    public function upsertRecords(AttendanceSession $session, string $markedById, array $rows): void
    {
        DB::transaction(function () use ($session, $markedById, $rows) {
            $lockedSession = AttendanceSession::query()->lockForUpdate()->findOrFail($session->getKey());

            if ($lockedSession->isLocked()) {
                throw new \RuntimeException('Attendance session is locked and cannot be changed.');
            }

            foreach ($rows as $row) {
                $studentId = $row['student_id'] ?? null;

                if (! $studentId || empty($row['status'])) {
                    continue;
                }

                $studentBelongsToClass = Student::query()
                    ->whereKey($studentId)
                    ->where('class_room_id', $lockedSession->class_room_id)
                    ->where('academic_year_id', $lockedSession->academic_year_id)
                    ->exists();

                if (! $studentBelongsToClass) {
                    throw new \DomainException('The selected student does not belong to this attendance class for the session academic year.');
                }

                AttendanceRecord::updateOrCreate(
                    ['attendance_session_id' => $lockedSession->getKey(), 'student_id' => $studentId],
                    [
                        'status' => $row['status'],
                        'note' => ! empty($row['note']) ? $row['note'] : null,
                        'marked_by_id' => $markedById,
                    ]
                );
            }
        });
    }

    public function closeSession(AttendanceSession $session): void
    {
        $session = DB::transaction(function () use ($session) {
            $session = AttendanceSession::query()->lockForUpdate()->findOrFail($session->getKey());

            if ($session->isLocked()) {
                return $session;
            }

            $session->update([
                'status' => AttendanceSessionStatus::Closed->value,
                'closed_at' => now(),
                'submitted_at' => $session->submitted_at ?? now(),
                'locked_at' => now(),
            ]);

            return $session;
        });

        $this->notifyAbsentParents($session);
    }

    private function notifyAbsentParents(AttendanceSession $session): void
    {
        if (! Setting::bool('parent_absence_notification', true)) {
            return;
        }

        $records = $session->records()
            ->with(['student.guardians.user'])
            ->where('status', AttendanceStatus::Absent->value)
            ->get();

        if ($records->isEmpty()) {
            return;
        }

        $notificationService = app(NotificationService::class);

        foreach ($records as $record) {
            $student = $record->student;

            foreach ($student->guardians as $guardian) {
                if (! $guardian->user_id || ! $guardian->canAccess('attendance', $student)) {
                    continue;
                }

                $notificationService->sendToUser($guardian->user_id, [
                    'type' => 'attendance',
                    'category' => 'attendance',
                    'priority' => 'high',
                    'icon' => 'alert-triangle',
                    'title' => __('Attendance alert'),
                    'body' => __('Your child :name was marked absent on :date.', [
                        'name' => $student->full_name,
                        'date' => $session->date->format('D, M j, Y'),
                    ]),
                    'redirect_url' => route('cms.parent.attendance'),
                ]);
            }
        }
    }

    public function unlockSession(AttendanceSession $session): void
    {
        DB::transaction(function () use ($session) {
            $session = AttendanceSession::query()->lockForUpdate()->findOrFail($session->getKey());

            $session->update([
                'status' => AttendanceSessionStatus::Open->value,
                'closed_at' => null,
                'submitted_at' => null,
                'locked_at' => null,
            ]);
        });
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
                $student = $this->resolveStudent($row, $class);

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

    public static function summarize(Collection $records): array
    {
        return (new self)->summary($records);
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

    public function rateFor(array $totals): ?float
    {
        $marked = array_sum($totals);

        if ($marked <= 0) {
            return null;
        }

        $attended = $totals['present'] + $totals['late'];

        if (Setting::bool('excused_counts_as_present', true)) {
            $attended += $totals['excused'];
        }

        return round(($attended / $marked) * 100, 1);
    }

    /* ------------------------------- Dashboard -------------------------------- */

    public function todayStats(): array
    {
        $today = Carbon::today()->toDateString();

        $sessions = AttendanceSession::with(['classRoom.gradeLevel', 'records'])
            ->whereDate('date', $today)
            ->orderByDesc('created_at')
            ->get();

        $totals = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
        ];

        foreach ($sessions as $session) {
            foreach ($this->summary($session->records) as $status => $count) {
                $totals[$status] += $count;
            }
        }

        $marked = array_sum($totals);
        $rate = $this->rateFor($totals);

        return [
            'date' => $today,
            'sessions' => $sessions,
            'sessions_count' => $sessions->count(),
            'closed_count' => $sessions->where(fn ($session) => $session->status === AttendanceSessionStatus::Closed)->count(),
            'open_count' => $sessions->where(fn ($session) => $session->isOpen())->count(),
            'marked' => $marked,
            'present' => $totals['present'],
            'absent' => $totals['absent'],
            'late' => $totals['late'],
            'excused' => $totals['excused'],
            'rate' => $rate,
            'by_grade' => $sessions->groupBy(fn ($session) => $session->classRoom?->grade_level_id ?? 'none'),
        ];
    }

    public function statusLabel(string|AttendanceStatus $status): string
    {
        return $status instanceof AttendanceStatus
            ? $status->label()
            : AttendanceStatus::from($status)->label();
    }

    /* -------------------------------- Reports --------------------------------- */

    public function dailyReport(string $date, ?string $gradeId = null, ?string $classId = null): array
    {
        $query = AttendanceSession::with(['classRoom.gradeLevel', 'records.student', 'takenBy'])
            ->whereDate('date', $date)
            ->when($classId, fn ($builder, $id) => $builder->where('class_room_id', $id))
            ->when($gradeId, fn ($builder, $id) => $builder->whereHas(
                'classRoom',
                fn ($classQuery) => $classQuery->where('grade_level_id', $id)
            ))
            ->orderByDesc('created_at');

        $sessions = $query->get();

        $totals = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
        ];

        foreach ($sessions as $session) {
            foreach ($this->summary($session->records) as $status => $count) {
                $totals[$status] += $count;
            }
        }

        $marked = array_sum($totals);
        $rate = $this->rateFor($totals);

        return [
            'date' => $date,
            'sessions' => $sessions,
            'totals' => $totals,
            'marked' => $marked,
            'rate' => $rate,
        ];
    }

    public function monthlyReport(Carbon $from, Carbon $to, ?string $gradeId = null, ?string $classId = null): Collection
    {
        $range = [$from->toDateString(), $to->toDateString()];

        return Student::query()
            ->with('classRoom.gradeLevel')
            ->whereHas('attendanceRecords', fn ($query) => $query->whereHas('session', fn ($q) => $q
                ->whereBetween('date', $range)
                ->when($classId, fn ($builder, $id) => $builder->where('class_room_id', $id))
                ->when($gradeId, fn ($builder, $id) => $builder->whereHas('classRoom', fn ($classQuery) => $classQuery->where('grade_level_id', $id)))
            ))
            ->withCount([
                'attendanceRecords as present_count' => fn ($query) => $query->where('status', AttendanceStatus::Present->value)->whereHas('session', fn ($q) => $q->whereBetween('date', $range)),
                'attendanceRecords as absent_count' => fn ($query) => $query->where('status', AttendanceStatus::Absent->value)->whereHas('session', fn ($q) => $q->whereBetween('date', $range)),
                'attendanceRecords as late_count' => fn ($query) => $query->where('status', AttendanceStatus::Late->value)->whereHas('session', fn ($q) => $q->whereBetween('date', $range)),
                'attendanceRecords as excused_count' => fn ($query) => $query->where('status', AttendanceStatus::Excused->value)->whereHas('session', fn ($q) => $q->whereBetween('date', $range)),
            ])
            ->orderBy('student_number')
            ->get()
            ->map(function (Student $student) {
                $total = $student->present_count + $student->absent_count + $student->late_count + $student->excused_count;

                return [
                    'student' => $student, 'total' => $total,
                    'present' => $student->present_count, 'absent' => $student->absent_count,
                    'late' => $student->late_count, 'excused' => $student->excused_count,
                    'rate' => $this->rateFor([
                        'present' => $student->present_count, 'absent' => $student->absent_count,
                        'late' => $student->late_count, 'excused' => $student->excused_count,
                    ]),
                ];
            });
    }
    public function studentReport(Student $student, ?string $from = null, ?string $to = null): array
    {
        $records = $student->attendanceRecords()
            ->with(['session.classRoom.gradeLevel', 'session.takenBy'])
            ->when($from, fn ($query, $date) => $query->whereHas('session', fn ($q) => $q->whereDate('date', '>=', $date)))
            ->when($to, fn ($query, $date) => $query->whereHas('session', fn ($q) => $q->whereDate('date', '<=', $date)))
            ->orderByDesc('created_at')
            ->get();

        $summary = $this->summary($records);
        $total = array_sum($summary);
        $rate = $this->rateFor($summary);

        return [
            'records' => $records,
            'summary' => $summary,
            'total' => $total,
            'rate' => $rate,
        ];
    }

    public function statusReport(AttendanceStatus $status, Carbon $from, Carbon $to, ?string $gradeId = null, ?string $classId = null): Collection
    {
        $range = [$from->toDateString(), $to->toDateString()];

        return AttendanceRecord::with(['student', 'session.classRoom.gradeLevel'])
            ->where('status', $status->value)
            ->whereHas('session', fn ($q) => $q
                ->whereBetween('date', $range)
                ->when($classId, fn ($builder, $id) => $builder->where('class_room_id', $id))
                ->when($gradeId, fn ($builder, $id) => $builder->whereHas('classRoom', fn ($classQuery) => $classQuery->where('grade_level_id', $id)))
            )
            ->orderByDesc('created_at')
            ->get();
    }
    public function trendReport(Carbon $from, Carbon $to, ?string $gradeId = null): Collection
    {
        $query = AttendanceSession::with('records')
            ->whereBetween('date', [$from, $to])
            ->when($gradeId, fn ($builder, $id) => $builder->whereHas(
                'classRoom',
                fn ($classQuery) => $classQuery->where('grade_level_id', $id)
            ))
            ->get();

        return $query
            ->groupBy(fn ($session) => $session->date->toDateString())
            ->map(function (Collection $sessions) {
                $totals = [
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'excused' => 0,
                ];

                foreach ($sessions as $session) {
                    foreach ($this->summary($session->records) as $status => $count) {
                        $totals[$status] += $count;
                    }
                }

                return $totals;
            })
            ->sortKeys();
    }

    public function completionReport(Carbon $from, Carbon $to, ?string $gradeId = null, ?string $classId = null): Collection
    {
        $classes = ClassRoom::with('gradeLevel')
            ->where('academic_year_id', $this->currentYear()->getKey())
            ->when($gradeId, fn ($builder, $id) => $builder->where('grade_level_id', $id))
            ->when($classId, fn ($builder, $id) => $builder->whereKey($id))
            ->orderBy('name')
            ->get();

        $sessions = AttendanceSession::withCount('records')
            ->whereBetween('date', [$from, $to])
            ->when($classId, fn ($builder, $id) => $builder->where('class_room_id', $id))
            ->get()
            ->groupBy('class_room_id');

        return $classes->map(function (ClassRoom $class) use ($sessions) {
            $classSessions = $sessions->get($class->getKey(), collect());

            return [
                'class' => $class,
                'sessions' => $classSessions,
                'session_count' => $classSessions->count(),
                'marked' => $classSessions->sum('records_count'),
            ];
        });
    }

    public function gradeReport(Carbon $from, Carbon $to): array
    {
        $rows = $this->monthlyReport($from, $to);

        $grouped = $rows
            ->groupBy(fn (array $row) => $row['student']->classRoom?->gradeLevel?->getKey() ?? 'none')
            ->sortBy(fn (Collection $group, string $key) => $key === 'none' ? 999 : ($group->first()['student']->classRoom->gradeLevel->sort_order ?? 999));

        $grades = $grouped->map(function (Collection $group) {
            $totals = [
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0,
            ];

            foreach ($group as $row) {
                foreach (['present', 'absent', 'late', 'excused'] as $key) {
                    $totals[$key] += $row[$key];
                }
            }

            $grade = $group->first()['student']->classRoom?->gradeLevel;

            return [
                'grade' => $grade,
                'students' => $group->count(),
                'classes' => $group
                    ->map(fn (array $row) => $row['student']->classRoom?->name)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all(),
                'totals' => $totals,
                'marked' => $group->sum('total'),
                'rate' => $this->rateFor($totals),
            ];
        })->values();

        $overall = [
            'present' => $grades->sum(fn ($row) => $row['totals']['present']),
            'absent' => $grades->sum(fn ($row) => $row['totals']['absent']),
            'late' => $grades->sum(fn ($row) => $row['totals']['late']),
            'excused' => $grades->sum(fn ($row) => $row['totals']['excused']),
        ];

        return [
            'grades' => $grades,
            'totals' => $overall,
            'marked' => $grades->sum('marked'),
            'rate' => $this->rateFor($overall),
        ];
    }

    public function studentsInScope(?string $gradeId = null, ?string $classId = null): int
    {
        return (int) Student::query()
            ->whereHas('classRoom', function ($query) use ($gradeId, $classId) {
                $query->when($gradeId, fn ($q, $id) => $q->where('grade_level_id', $id));
                $query->when($classId, fn ($q, $id) => $q->where('id', $id));
            })
            ->distinct()
            ->count('id');
    }

    public function lateReport(Carbon $from, Carbon $to, ?string $gradeId = null, ?string $classId = null): Collection
    {
        $range = [$from->toDateString(), $to->toDateString()];

        return AttendanceRecord::with(['student.classRoom.gradeLevel', 'session'])
            ->where('status', AttendanceStatus::Late->value)
            ->whereHas('session', fn ($q) => $q
                ->whereBetween('date', $range)
                ->when($classId, fn ($builder, $id) => $builder->where('class_room_id', $id))
                ->when($gradeId, fn ($builder, $id) => $builder->whereHas('classRoom', fn ($classQuery) => $classQuery->where('grade_level_id', $id)))
            )
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('student_id')
            ->map(function (Collection $rows) {
                $first = $rows->first();

                return [
                    'student' => $first->student,
                    'late_count' => $rows->count(),
                    'last_late' => $first?->session?->date,
                    'class' => $first->student->classRoom,
                ];
            })
            ->sortByDesc('late_count')
            ->values();
    }
    public function pendingCorrections(): Collection
    {
        return AttendanceCorrection::with(['record.student', 'record.session.classRoom.gradeLevel', 'requestedBy'])
            ->pending()
            ->latest()
            ->get();
    }

    public function corrections(?string $status = null, ?string $gradeId = null): Collection
    {
        return AttendanceCorrection::with(['record.student', 'record.session.classRoom.gradeLevel', 'requestedBy', 'reviewedBy'])
            ->when($status, fn ($builder, $value) => $builder->where('status', $value))
            ->when($gradeId, fn ($builder, $id) => $builder->whereHas(
                'record.session.classRoom',
                fn ($q) => $q->where('grade_level_id', $id)
            ))
            ->latest()
            ->get();
    }

    public function requestCorrection(AttendanceRecord $record, string $status, string $reason, string $requestedById): AttendanceCorrection
    {
        return DB::transaction(function () use ($record, $status, $reason, $requestedById) {
            $record = AttendanceRecord::query()
                ->with('session')
                ->lockForUpdate()
                ->findOrFail($record->getKey());

            if (! $record->session?->isLocked()) {
                throw new \DomainException('Attendance corrections can only be requested for locked sessions.');
            }

            $current = $record->status instanceof AttendanceStatus ? $record->status->value : $record->status;
            $requested = $status instanceof AttendanceStatus ? $status->value : $status;

            if (! in_array($requested, AttendanceStatus::values(), true)) {
                throw new \DomainException('Invalid attendance status.');
            }

            if ($current === $requested) {
                throw new \DomainException('The requested status is already recorded.');
            }

            if (AttendanceCorrection::query()
                ->where('attendance_record_id', $record->getKey())
                ->pending()
                ->exists()) {
                throw new \DomainException('A correction request is already pending for this attendance record.');
            }

            $approvalRequired = Setting::bool('correction_approval_required', true);

            if (! $approvalRequired) {
                $record->update(['status' => $requested]);

                return AttendanceCorrection::create([
                    'attendance_record_id' => $record->getKey(),
                    'requested_by_id' => $requestedById,
                    'requested_status' => $requested,
                    'old_status' => $current,
                    'new_status' => $requested,
                    'status' => AttendanceCorrectionStatus::Approved->value,
                    'reviewed_by_id' => $requestedById,
                    'reviewer_note' => 'Auto-approved: manual approval is not required.',
                    'reason' => $reason,
                    'submitted_at' => now(),
                    'reviewed_at' => now(),
                ]);
            }

            return AttendanceCorrection::create([
                'attendance_record_id' => $record->getKey(),
                'requested_by_id' => $requestedById,
                'requested_status' => $requested,
                'old_status' => $current,
                'status' => AttendanceCorrectionStatus::Pending->value,
                'reason' => $reason,
                'submitted_at' => now(),
            ]);
        });
    }

    public function reviewCorrection(AttendanceCorrection $correction, bool $approve, string $reviewerId, ?string $note = null): void
    {
        DB::transaction(function () use ($correction, $approve, $reviewerId, $note) {
            $correction = AttendanceCorrection::query()
                ->with('record.session')
                ->lockForUpdate()
                ->findOrFail($correction->getKey());

            if (! $correction->isPending()) {
                throw new \DomainException('Only pending attendance corrections can be reviewed.');
            }

            $record = $correction->record;

            if ($record === null || ! $record->session?->isLocked()) {
                throw new \DomainException('The attendance record is no longer eligible for correction.');
            }

            if ($approve && $record !== null) {
                $record->update([
                    'status' => $correction->requested_status instanceof AttendanceStatus
                        ? $correction->requested_status->value
                        : $correction->requested_status,
                ]);
            }

            $correction->update([
                'status' => $approve ? AttendanceCorrectionStatus::Approved->value : AttendanceCorrectionStatus::Rejected->value,
                'reviewed_by_id' => $reviewerId,
                'reviewer_note' => $note ?: null,
                'new_status' => $approve ? $correction->requested_status->value : $correction->old_status,
                'reviewed_at' => now(),
            ]);
        });
    }


    /* --------------------------------- Alerts ---------------------------------- */

    public function studentAlerts(int $absentThreshold = 3, int $lateThreshold = 5): Collection
    {
        $since = Carbon::now()->startOfMonth()->toDateString();

        $students = Student::with('classRoom.gradeLevel')
            ->whereHas('attendanceRecords', fn ($query) => $query->whereHas('session', fn ($q) => $q->whereDate('date', '>=', $since)))
            ->withCount([
                'attendanceRecords as absent_count' => fn ($query) => $query
                    ->where('status', AttendanceStatus::Absent->value)
                    ->whereHas('session', fn ($q) => $q->whereDate('date', '>=', $since)),
                'attendanceRecords as late_count' => fn ($query) => $query
                    ->where('status', AttendanceStatus::Late->value)
                    ->whereHas('session', fn ($q) => $q->whereDate('date', '>=', $since)),
            ])
            ->get()
            ->filter(fn (Student $student) => $student->absent_count >= $absentThreshold || $student->late_count >= $lateThreshold)
            ->sortByDesc(fn (Student $student) => $student->absent_count + $student->late_count)
            ->values();

        return $students->map(function (Student $student) use ($absentThreshold, $lateThreshold) {
            $flag = collect();
            if ($student->absent_count >= $absentThreshold) $flag->push('Absenteeism');
            if ($student->late_count >= $lateThreshold) $flag->push('Chronic lateness');

            return [
                'student' => $student,
                'absent' => $student->absent_count,
                'late' => $student->late_count,
                'flags' => $flag,
            ];
        });
    }
    private function resolveStudent(array $row, ClassRoom $class): ?Student
    {
        $query = Student::query()
            ->where('class_room_id', $class->getKey())
            ->where('academic_year_id', $class->academic_year_id);

        if (! empty($row['student_id'])) {
            return $query->whereKey($row['student_id'])->first();
        }

        if (! empty($row['student_number'])) {
            return $query->where('student_number', $row['student_number'])->first();
        }

        return null;
    }
}

<?php

namespace App\Domains\Attendance\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Attendance\Models\AttendanceCorrection;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Requests\StartAttendanceRequest;
use App\Domains\Attendance\Requests\SyncAttendanceRequest;
use App\Domains\Attendance\Requests\UpdateAttendanceRequest;
use App\Domains\Attendance\Requests\UpdateAttendanceSettingsRequest;
use App\Domains\Attendance\Services\AttendanceService;
use App\Domains\Settings\Models\Setting;
use App\Domains\Students\Models\Student;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\AttendanceSessionStatus;
use App\Support\Enums\AttendanceStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function index(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);

        $currentYear = $this->attendanceService->currentYear();

        $sessions = AttendanceSession::query()
            ->with(['classRoom.gradeLevel', 'classRoom.academicYear', 'takenBy'])
            ->when($this->isTeacher(), fn ($query) => $query->whereIn('class_room_id', $this->teacherClassRoomIds()))
            ->withCount('records')
            ->when(request('class_room_id'), fn ($query, $classId) => $query->where('class_room_id', $classId))
            ->when(request('grade_level_id'), function ($query, $gradeId) {
                $query->whereHas('classRoom', fn ($classQuery) => $classQuery->where('grade_level_id', $gradeId));
            })
            ->when(request('date'), fn ($query, $date) => $query->forDate($date))
            ->when(request('status') === AttendanceSessionStatus::Open->value, fn ($query) => $query->open())
            ->when(request('status') === AttendanceSessionStatus::Closed->value, fn ($query) => $query->where('status', AttendanceSessionStatus::Closed->value))
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $classes = $this->classesForYear($currentYear->getKey());
        if ($this->isTeacher()) {
            $classes = $classes->whereIn('id', $this->teacherClassRoomIds())->values();
        }

        $openCount = AttendanceSession::query()
            ->open()
            ->when($this->isTeacher(), fn ($query) => $query->whereIn('class_room_id', $this->teacherClassRoomIds()))
            ->count();
        $todayCount = AttendanceSession::query()
            ->forDate(Carbon::today()->format('Y-m-d'))
            ->when($this->isTeacher(), fn ($query) => $query->whereIn('class_room_id', $this->teacherClassRoomIds()))
            ->count();

        return view('attendance.index', compact('sessions', 'classes', 'currentYear', 'openCount', 'todayCount'));
    }

    public function create(): View
    {
        $this->authorize('create', AttendanceSession::class);

        $currentYear = $this->attendanceService->currentYear();

        $classes = $this->classesForYear($currentYear->getKey());
        if ($this->isTeacher()) {
            $classes = $classes->whereIn('id', $this->teacherClassRoomIds())->values();
        }

        $today = Carbon::today()->format('Y-m-d');

        return view('attendance.create', compact('classes', 'currentYear', 'today'));
    }

    public function store(StartAttendanceRequest $request): RedirectResponse
    {
        $this->authorize('create', AttendanceSession::class);

        $classRoom = ClassRoom::findOrFail($request->validated('class_room_id'));

        if ($this->isTeacher() && ! in_array($classRoom->getKey(), $this->teacherClassRoomIds(), true)) {
            abort(403);
        }

        try {
            $session = $this->attendanceService->openSession(
            $classRoom,
            $request->validated('date'),
            $request->user()->id
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['date' => $e->getMessage()])->withInput();
        }

        ActivityLogger::log(
            'opened attendance for '.$classRoom->name.' on '.$session->date,
            'attendance',
            $session->id
        );

        return redirect()
            ->route('attendance.show', $session)
            ->with('status', 'Attendance session ready. Mark it below.');
    }

    public function show(AttendanceSession $session): View
    {
        $this->authorize('view', $session);

        $session->load([
            'classRoom.gradeLevel',
            'classRoom.academicYear',
            'takenBy',
            'records.student',
            'records.corrections',
        ]);

        $summary = $this->attendanceService->summary($session->records);

        $recordMap = $session->records->keyBy('student_id');

        $boardRows = $session->classRoom->students()
            ->orderBy('student_number')
            ->get()
            ->map(function ($student) use ($recordMap) {
                $record = $recordMap->get($student->getKey());

                return [
                    'student_id' => $student->id,
                    'number' => $student->student_number,
                    'name' => $student->full_name,
                    'status' => $record?->status->value ?? AttendanceStatus::Present->value,
                    'note' => $record?->note ?? '',
                    'record_id' => $record?->getKey(),
                ];
            })
            ->values()
            ->all();

        return view('attendance.show', compact('session', 'summary', 'boardRows'));
    }

    public function update(UpdateAttendanceRequest $request, AttendanceSession $session): RedirectResponse
    {
        $this->authorize('update', $session);

        if (! $session->isOpen()) {
            return redirect()
                ->route('attendance.show', $session)
                ->withErrors(['records' => 'This session is locked and can no longer be edited directly. Request a correction instead.']);
        }

        try {
            $this->attendanceService->upsertRecords($session, $request->user()->id, $request->validated('records'));
        } catch (\DomainException $e) {
            return back()->withErrors(['records' => $e->getMessage()])->withInput();
        }

        ActivityLogger::log('recorded attendance for '.$session->classRoom->name.' on '.$session->date, 'attendance', $session->id);

        return redirect()
            ->route('attendance.show', $session)
            ->with('status', 'Attendance saved.');
    }

    public function close(AttendanceSession $session): RedirectResponse
    {
        $this->authorize('close', $session);

        if ($session->isOpen()) {
            $this->attendanceService->closeSession($session);

            ActivityLogger::log('closed and locked attendance for '.$session->classRoom->name.' on '.$session->date, 'attendance', $session->id);
        }

        return redirect()
            ->route('attendance.show', $session)
            ->with('status', 'Attendance session submitted and locked.');
    }

    public function override(AttendanceSession $session): RedirectResponse
    {
        abort_unless($session->isLocked() && auth()->user()?->hasPermission('attendance.configure'), 403);

        $this->attendanceService->unlockSession($session);

        ActivityLogger::log(
            'unlocked attendance for '.$session->classRoom->name.' on '.$session->date,
            'attendance',
            $session->id
        );

        return redirect()
            ->route('attendance.show', $session)
            ->with('status', 'Session unlocked for direct editing.');
    }

    public function dashboard(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);

        $currentYear = $this->attendanceService->currentYear();
        $today = $this->attendanceService->todayStats($this->isTeacher() ? $this->teacherClassRoomIds() : null);

        return view('attendance.dashboard', compact('today', 'currentYear'));
    }

    /* -------------------------------- Reports ---------------------------------- */

    public function reports(): RedirectResponse
    {
        $this->authorize('viewAny', AttendanceSession::class);

        return redirect()->route('attendance.reports.daily');
    }

    public function reportsDaily(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);
        $this->assertReportAccess();

        $currentYear = $this->attendanceService->currentYear();
        $classes = $this->classesForYear($currentYear->getKey());
        $grades = $classes->pluck('gradeLevel')->filter()->unique('id')->values();

        $report = $this->attendanceService->dailyReport(
            request('date', Carbon::today()->toDateString()),
            request('grade_level_id'),
            request('class_room_id')
        );

        return view('attendance.reports.daily', compact('report', 'classes', 'grades', 'currentYear'));
    }

    public function reportsStudent(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);
        $this->assertReportAccess();

        $from = request('from', Carbon::now()->startOfYear()->toDateString());
        $to = request('to', Carbon::today()->toDateString());

        $student = request('student_id') ? Student::with('classRoom')->find(request('student_id')) : null;

        $report = $student
            ? $this->attendanceService->studentReport($student, $from, $to)
            : null;

        $students = Student::with('classRoom')
            ->when(request('q'), function ($query, $search) {
                $query->where(function ($where) use ($search) {
                    $where
                        ->where('student_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('student_number')
            ->get();

        return view('attendance.reports.student', compact('report', 'student', 'students', 'from', 'to'));
    }

    public function reportsMonthly(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);
        $this->assertReportAccess();

        $currentYear = $this->attendanceService->currentYear();

        $from = request('from', Carbon::now()->startOfMonth()->toDateString());
        $to = request('to', Carbon::now()->endOfMonth()->toDateString());

        $rows = $this->attendanceService->monthlyReport(
            Carbon::parse($from),
            Carbon::parse($to),
            request('grade_level_id'),
            request('class_room_id')
        );

        $totals = [
            'present' => $rows->sum('present'),
            'absent' => $rows->sum('absent'),
            'late' => $rows->sum('late'),
            'excused' => $rows->sum('excused'),
        ];

        return view('attendance.reports.monthly', compact('rows', 'totals', 'from', 'to', 'currentYear'));
    }

    public function reportsStatus(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);
        $this->assertReportAccess();

        $currentYear = $this->attendanceService->currentYear();
        $classes = $this->classesForYear($currentYear->getKey());
        $grades = $classes->pluck('gradeLevel')->filter()->unique('id')->values();

        $status = AttendanceStatus::tryFrom((string) request('status', AttendanceStatus::Absent->value)) ?? AttendanceStatus::Absent;

        $from = request('from', Carbon::now()->startOfMonth()->toDateString());
        $to = request('to', Carbon::now()->endOfMonth()->toDateString());

        $rows = $this->attendanceService->statusReport(
            $status,
            Carbon::parse($from),
            Carbon::parse($to),
            request('grade_level_id'),
            request('class_room_id')
        );

        return view('attendance.reports.status', compact('rows', 'status', 'from', 'to', 'classes', 'grades', 'currentYear'));
    }

    public function reportsTrend(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);
        $this->assertReportAccess();

        $from = request('from', Carbon::now()->startOfMonth()->toDateString());
        $to = request('to', Carbon::now()->endOfMonth()->toDateString());

        $rows = $this->attendanceService->trendReport(
            Carbon::parse($from),
            Carbon::parse($to),
            request('grade_level_id')
        );

        return view('attendance.reports.trend', compact('rows', 'from', 'to'));
    }

    public function reportsCompletion(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);
        $this->assertReportAccess();

        $currentYear = $this->attendanceService->currentYear();

        $from = request('from', Carbon::now()->startOfMonth()->toDateString());
        $to = request('to', Carbon::now()->endOfMonth()->toDateString());

        $rows = $this->attendanceService->completionReport(
            Carbon::parse($from),
            Carbon::parse($to),
            request('grade_level_id')
        );

        return view('attendance.reports.completion', compact('rows', 'from', 'to', 'currentYear'));
    }

    public function reportsClass(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);
        $this->assertReportAccess();

        $currentYear = $this->attendanceService->currentYear();
        $classes = $this->classesForYear($currentYear->getKey());
        $grades = $classes->pluck('gradeLevel')->filter()->unique('id')->values();

        $from = request('from', Carbon::now()->startOfMonth()->toDateString());
        $to = request('to', Carbon::now()->endOfMonth()->toDateString());
        $classRoom = request('class_room_id') ? ClassRoom::find(request('class_room_id')) : $classes->first();

        $rows = $this->attendanceService->monthlyReport(
            Carbon::parse($from),
            Carbon::parse($to),
            null,
            $classRoom?->getKey()
        );

        $totals = [
            'present' => $rows->sum('present'),
            'absent' => $rows->sum('absent'),
            'late' => $rows->sum('late'),
            'excused' => $rows->sum('excused'),
        ];

        return view('attendance.reports.class', compact('rows', 'totals', 'from', 'to', 'currentYear', 'classes', 'grades', 'classRoom'));
    }

    public function reportsGrade(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);
        $this->assertReportAccess();

        $currentYear = $this->attendanceService->currentYear();

        $from = request('from', Carbon::now()->startOfMonth()->toDateString());
        $to = request('to', Carbon::now()->endOfMonth()->toDateString());

        $report = $this->attendanceService->gradeReport(Carbon::parse($from), Carbon::parse($to));

        return view('attendance.reports.grade', compact('report', 'from', 'to', 'currentYear'));
    }

    public function reportsLate(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);
        $this->assertReportAccess();

        $currentYear = $this->attendanceService->currentYear();
        $classes = $this->classesForYear($currentYear->getKey());
        $grades = $classes->pluck('gradeLevel')->filter()->unique('id')->values();

        $from = request('from', Carbon::now()->startOfMonth()->toDateString());
        $to = request('to', Carbon::now()->endOfMonth()->toDateString());

        $rows = $this->attendanceService->lateReport(
            Carbon::parse($from),
            Carbon::parse($to),
            request('grade_level_id'),
            request('class_room_id')
        );

        $totalStudents = $this->attendanceService->studentsInScope(
            request('grade_level_id'),
            request('class_room_id')
        );

        $rate = $totalStudents > 0
            ? round(($rows->count() / $totalStudents) * 100, 1)
            : null;

        return view('attendance.reports.late', compact('rows', 'rate', 'from', 'to', 'classes', 'grades', 'currentYear'));
    }

    public function reportsTerm(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);
        $this->assertReportAccess();

        $currentYear = $this->attendanceService->currentYear();

        $years = AcademicYear::orderByDesc('start_date')->get();

        $year = $years->firstWhere('id', request('academic_year_id'))
            ?? $years->firstWhere('is_current', true)
            ?? $years->first();

        $terms = ($year ?? $currentYear)->terms()->ordered()->get();

        $term = request('term_id')
            ? ($year ?? $currentYear)->terms()->find(request('term_id'))
            : $terms->firstWhere('is_current', true) ?? $terms->first();

        $from = $term?->start_date?->toDateString() ?? Carbon::now()->startOfMonth()->toDateString();
        $to = $term?->end_date?->toDateString() ?? Carbon::now()->endOfMonth()->toDateString();

        $rows = $this->attendanceService->monthlyReport(Carbon::parse($from), Carbon::parse($to));

        $totals = [
            'present' => $rows->sum('present'),
            'absent' => $rows->sum('absent'),
            'late' => $rows->sum('late'),
            'excused' => $rows->sum('excused'),
        ];

        $completion = $this->attendanceService->completionReport(Carbon::parse($from), Carbon::parse($to));

        return view('attendance.reports.term', compact('years', 'year', 'terms', 'term', 'rows', 'totals', 'completion', 'from', 'to', 'currentYear'));
    }

    /* ------------------------------ Corrections -------------------------------- */

    public function corrections(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);

        $rows = $this->attendanceService->corrections(
            request('status'),
            request('grade_level_id')
        );

        $pending = $this->attendanceService->pendingCorrections()->count();

        return view('attendance.corrections', compact('rows', 'pending'));
    }

    public function correctionsReview(Request $request, AttendanceCorrection $correction): RedirectResponse
    {
        abort_unless($correction->isPending(), 403);
        abort_unless(auth()->user()?->hasPermission('attendance.approve'), 403);

        $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'reviewer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $approve = $request->input('decision') === 'approve';

        $this->attendanceService->reviewCorrection(
            $correction,
            $approve,
            $request->user()->id,
            $request->input('reviewer_note')
        );

        ActivityLogger::log(
            ($approve ? 'approved' : 'rejected').' an attendance correction for '.($correction->record?->student?->full_name ?? 'student'),
            'attendance',
            $correction->getKey()
        );

        return back()->with(
            'status',
            $approve ? 'Correction approved and applied.' : 'Correction rejected.'
        );
    }

    /* --------------------------------- Alerts ---------------------------------- */

    public function alerts(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);

        $absentThreshold = (int) Setting::value('absence_alert_threshold', config('attendance.defaults.absence_alert_threshold', 3));
        $lateThreshold = (int) Setting::value('late_alert_threshold', config('attendance.defaults.late_alert_threshold', 5));

        $rows = $this->attendanceService->studentAlerts($absentThreshold, $lateThreshold, $this->isTeacher() ? $this->teacherClassRoomIds() : null);

        return view('attendance.alerts', compact('rows', 'absentThreshold', 'lateThreshold'));
    }

    /* -------------------------------- Settings --------------------------------- */

    public function settings(): View
    {
        abort_unless(auth()->user()?->hasPermission('attendance.configure'), 403);

        $defaults = $this->attendanceDefaults();

        $stored = Setting::where('group', 'attendance')->pluck('value', 'key')->all();

        foreach ($stored as $key => $value) {
            if (array_key_exists($key, $defaults) && is_bool($defaults[$key])) {
                $stored[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
        }

        $settings = array_replace($defaults, $stored);

        return view('attendance.settings', compact('settings'));
    }

    public function settingsUpdate(UpdateAttendanceSettingsRequest $request): RedirectResponse
    {
        $booleanKeys = [
            'excused_counts_as_present',
            'parent_absence_notification',
            'correction_approval_required',
        ];

        foreach ($request->validated() as $key => $value) {
            $stored = in_array($key, $booleanKeys, true)
                ? ($request->boolean($key) ? '1' : '0')
                : ((string) $value);

            Setting::set($key, $stored, 'attendance');
        }

        Cache::forget('settings');

        ActivityLogger::log('updated attendance settings', 'attendance');

        return back()->with('status', 'Attendance settings saved.');
    }

    public function sync(SyncAttendanceRequest $request)
    {
        $this->authorize('create', AttendanceSession::class);

        $result = $this->attendanceService->mergeOfflineRecords($request->validated('records', []));

        return response()->json($result);
    }


    private function isTeacher(): bool
    {
        return auth()->user()?->hasRole('teacher') === true;
    }

    private function teacherClassRoomIds(): array
    {
        return ClassSubject::query()
            ->where('teacher_id', auth()->id())
            ->pluck('class_room_id')
            ->unique()
            ->values()
            ->all();
    }

    private function assertReportAccess(): void
    {
        abort_unless(! $this->isTeacher(), 403);
    }

    private function classesForYear(string $academicYearId): Collection
    {
        return ClassRoom::with('gradeLevel')
            ->where('academic_year_id', $academicYearId)
            ->orderBy('name')
            ->get();
    }

    private function attendanceDefaults(): array
    {
        return [
            'attendance_mode' => 'daily',
            'start_time' => '08:00',
            'end_time' => '15:00',
            'late_threshold_minutes' => 10,
            'excused_counts_as_present' => true,
            'absence_alert_threshold' => 3,
            'late_alert_threshold' => 5,
            'parent_absence_notification' => true,
            'correction_approval_required' => true,
        ];
    }
}

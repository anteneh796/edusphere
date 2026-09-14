<?php

namespace App\Domains\Attendance\Controllers;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Requests\StartAttendanceRequest;
use App\Domains\Attendance\Requests\SyncAttendanceRequest;
use App\Domains\Attendance\Requests\UpdateAttendanceRequest;
use App\Domains\Attendance\Services\AttendanceService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\AttendanceSessionStatus;
use App\Support\Enums\AttendanceStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function index(): View
    {
        $this->authorize('viewAny', AttendanceSession::class);

        $currentYear = $this->attendanceService->currentYear();

        $sessions = AttendanceSession::query()
            ->with(['classRoom.gradeLevel', 'classRoom.academicYear', 'takenBy'])
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

        $classes = ClassRoom::where('academic_year_id', $currentYear->getKey())
            ->with('gradeLevel')
            ->orderBy('name')
            ->get();

        $openCount = AttendanceSession::open()->count();
        $todayCount = AttendanceSession::forDate(Carbon::today()->format('Y-m-d'))->count();

        return view('attendance.index', compact('sessions', 'classes', 'currentYear', 'openCount', 'todayCount'));
    }

    public function create(): View
    {
        $this->authorize('create', AttendanceSession::class);

        $currentYear = $this->attendanceService->currentYear();

        $classes = ClassRoom::with('gradeLevel')
            ->where('academic_year_id', $currentYear->getKey())
            ->orderBy('name')
            ->get();

        $today = Carbon::today()->format('Y-m-d');

        return view('attendance.create', compact('classes', 'currentYear', 'today'));
    }

    public function store(StartAttendanceRequest $request): RedirectResponse
    {
        $this->authorize('create', AttendanceSession::class);

        $classRoom = ClassRoom::findOrFail($request->validated('class_room_id'));

        $session = $this->attendanceService->openSession(
            $classRoom,
            $request->validated('date'),
            $request->user()->id
        );

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
                ->withErrors(['records' => 'This session is closed and can no longer be edited.']);
        }

        $this->attendanceService->upsertRecords($session, $request->user()->id, $request->validated('records'));

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

            ActivityLogger::log('closed attendance for '.$session->classRoom->name.' on '.$session->date, 'attendance', $session->id);
        }

        return redirect()
            ->route('attendance.show', $session)
            ->with('status', 'Attendance session closed.');
    }

    public function sync(SyncAttendanceRequest $request)
    {
        $this->authorize('create', AttendanceSession::class);

        $result = $this->attendanceService->mergeOfflineRecords($request->validated('records', []));

        return response()->json($result);
    }
}

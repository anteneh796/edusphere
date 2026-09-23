<?php

namespace App\Domains\Portals\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User as AccountUser;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Requests\AttendanceCorrectionRequest;
use App\Domains\Attendance\Services\AttendanceService;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\ParentPortal\Models\MeetingRequest;
use App\Domains\Students\Models\Student;
use App\Domains\TeacherPortal\Models\AssessmentResult;
use App\Domains\TeacherPortal\Models\BehaviorNote;
use App\Domains\TeacherPortal\Models\ClassroomAssessment;
use App\Domains\TeacherPortal\Models\CurriculumUnit;
use App\Domains\TeacherPortal\Models\HomeworkAssignment;
use App\Domains\TeacherPortal\Models\LessonPlan;
use App\Domains\TeacherPortal\Models\TeacherMessage;
use App\Domains\TeacherPortal\Models\TeachingResource;
use App\Domains\TeacherPortal\Models\TimetableSlot;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\AttendanceStatus;
use App\Support\Enums\MeetingRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherPortalController extends Controller
{
    /* ---------------------------------- Helpers ---------------------------------- */

    private function currentYear(): ?AcademicYear
    {
        return AcademicYear::current()->orderBy('start_date')->first();
    }

    private function myClassSubjectQuery(): Builder
    {
        return ClassSubject::query()
            ->with(['subject', 'classRoom.gradeLevel'])
            ->where('teacher_id', auth()->id());
    }

    private function myClassSubjectIds(): Collection
    {
        return ClassSubject::query()->where('teacher_id', auth()->id())->pluck('id');
    }

    private function myClassRoomIds(): Collection
    {
        return ClassSubject::query()
            ->where('teacher_id', auth()->id())
            ->pluck('class_room_id')
            ->unique()
            ->values();
    }

    private function myClassSubjectInRule(): string
    {
        return 'in:'.$this->myClassSubjectIds()->implode(',');
    }

    private function myClassRoomInRule(): string
    {
        return 'in:'.$this->myClassRoomIds()->implode(',');
    }

    private function authorizeClassSubject(ClassSubject $classSubject): void
    {
        abort_unless((string) $classSubject->teacher_id === (string) auth()->id(), 403);
    }

    private function authorizeClassroom(AttendanceSession $session): void
    {
        abort_unless($this->myClassRoomIds()->contains($session->class_room_id), 403);
    }

    private function authorizeOwned(Model $model): void
    {
        abort_unless((string) $model->teacher_id === (string) auth()->id(), 403);
    }

    public function dashboard(): View
    {
        $subjects = $this->myClassSubjectQuery()->orderBy('position')->get();
        $subjectIds = $subjects->pluck('id');
        $classRoomIds = $subjects->pluck('class_room_id')->unique()->values();

        return view('portals.teacher.dashboard', [
            'subjects' => $subjects,
            'students' => Student::query()->whereIn('class_room_id', $classRoomIds)->count(),
            'todaySessions' => AttendanceSession::query()
                ->whereIn('class_room_id', $classRoomIds)
                ->forDate(now()->toDateString())
                ->with(['classRoom.gradeLevel'])
                ->get(),
            'recentLessonPlans' => LessonPlan::query()
                ->whereIn('class_subject_id', $subjectIds)
                ->with(['classSubject.subject'])
                ->latest('scheduled_date')
                ->limit(6)
                ->get(),
            'upcomingHomework' => HomeworkAssignment::query()
                ->whereIn('class_subject_id', $subjectIds)
                ->where('status', 'published')
                ->with(['classSubject.subject'])
                ->latest('due_on')
                ->limit(6)
                ->get(),
            'todayTimetable' => $this->todayTimetable($classRoomIds),
        ]);
    }

    public function profile(): View
    {
        $classSubjects = $this->myClassSubjectQuery()->orderBy('position')->get();
        $subjectIds = $classSubjects->pluck('id');

        return view('portals.teacher.profile', [
            'user' => auth()->user()->load('roles'),
            'classSubjects' => $classSubjects,
            'lessonPlanCount' => LessonPlan::query()->whereIn('class_subject_id', $subjectIds)->count(),
            'homeworkCount' => HomeworkAssignment::query()->whereIn('class_subject_id', $subjectIds)->count(),
            'assessmentCount' => ClassroomAssessment::query()->whereIn('class_subject_id', $subjectIds)->count(),
        ]);
    }

    public function timetable(): View
    {
        $slots = TimetableSlot::query()
            ->whereIn('class_room_id', $this->myClassRoomIds())
            ->when($this->currentYear(), fn (Builder $query, AcademicYear $year) => $query->where('academic_year_id', $year->getKey()))
            ->with(['classSubject.subject', 'classRoom.gradeLevel'])
            ->orderBy('period_number')
            ->get();

        return view('portals.teacher.timetable', [
            'slots' => $slots,
            'periods' => $slots->pluck('period_number')->unique()->sort()->values(),
            'days' => [1, 2, 3, 4, 5],
        ]);
    }

    public function classes(): View
    {
        return view('portals.teacher.classes', [
            'subjects' => $this->myClassSubjectQuery()->orderBy('position')->get(),
        ]);
    }

    public function classShow(ClassSubject $classSubject): View
    {
        $this->authorizeClassSubject($classSubject);

        return view('portals.teacher.class-show', [
            'classSubject' => $classSubject->load(['subject', 'classRoom.gradeLevel', 'classRoom.students']),
        ]);
    }

    private function todayTimetable(Collection $classRoomIds): Collection
    {
        if ($classRoomIds->isEmpty()) {
            return collect();
        }

        $today = (int) now()->dayOfWeek;

        return TimetableSlot::query()
            ->whereIn('class_room_id', $classRoomIds)
            ->where('day_of_week', $today === 0 ? 7 : $today)
            ->when($this->currentYear(), fn (Builder $query, AcademicYear $year) => $query->where('academic_year_id', $year->getKey()))
            ->with(['classSubject.subject', 'classRoom.gradeLevel'])
            ->orderBy('period_number')
            ->get();
    }

    public function attendance(): View
    {
        $sessions = AttendanceSession::query()
            ->whereIn('class_room_id', $this->myClassRoomIds())
            ->with(['classRoom.gradeLevel'])
            ->withCount('records')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        return view('portals.teacher.attendance', [
            'sessions' => $sessions,
            'classRooms' => ClassRoom::query()
                ->whereKey($this->myClassRoomIds())
                ->with('gradeLevel')
                ->orderBy('name')
                ->get(),
            'currentYear' => $this->currentYear(),
        ]);
    }

    public function attendanceSession(AttendanceSession $session): View
    {
        $this->authorizeClassroom($session);

        $session->load(['classRoom.gradeLevel', 'classRoom.academicYear', 'takenBy', 'records.student']);

        $summary = (new AttendanceService)->summary($session->records);

        $recordMap = $session->records->keyBy('student_id');

        $boardRows = $session->classRoom->students()
            ->orderBy('student_number')
            ->get()
            ->map(function (Student $student) use ($recordMap) {
                $record = $recordMap->get($student->getKey());

                return [
                    'student_id' => $student->getKey(),
                    'number' => $student->student_number,
                    'name' => $student->full_name,
                    'status' => $record?->status->value ?? AttendanceStatus::Present->value,
                    'note' => $record?->note ?? '',
                ];
            })
            ->values()
            ->all();

        return view('portals.teacher.attendance-session', compact('session', 'summary', 'boardRows'));
    }

    public function attendanceStore(): RedirectResponse
    {
        $validated = request()->validate([
            'class_room_id' => ['required', 'exists:class_rooms,id', $this->myClassRoomInRule()],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $classRoom = ClassRoom::findOrFail($validated['class_room_id']);

        $session = (new AttendanceService)->openSession($classRoom, $validated['date'], auth()->id());

        return to_route('cms.teacher.attendance.session', $session)->with('status', __('Attendance session ready. Mark it below.'));
    }

    public function attendanceUpdate(AttendanceSession $session): RedirectResponse
    {
        $this->authorizeClassroom($session);

        $validated = request()->validate([
            'records' => ['required', 'array'],
            'records.*.student_id' => ['required', 'string'],
            'records.*.status' => ['required', Rule::in(array_map(fn ($status) => $status->value, AttendanceStatus::cases()))],
            'records.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $session->isOpen()) {
            return to_route('cms.teacher.attendance.session', $session)
                ->withErrors(['records' => __('This session is closed and can no longer be edited.')]);
        }

        (new AttendanceService)->upsertRecords($session, auth()->id(), $validated['records']);

        return to_route('cms.teacher.attendance.session', $session)->with('status', __('Attendance saved.'));
    }

    public function attendanceClose(AttendanceSession $session): RedirectResponse
    {
        $this->authorizeClassroom($session);

        if ($session->isOpen()) {
            (new AttendanceService)->closeSession($session);
            ActivityLogger::log('closed and locked attendance for '.$session->classRoom->name.' on '.$session->date, 'attendance', $session->getKey());
        }

        return to_route('cms.teacher.attendance.session', $session)->with('status', __('Attendance session submitted and locked.'));
    }

    public function attendanceCorrectionRequest(AttendanceSession $session, AttendanceCorrectionRequest $request): RedirectResponse
    {
        $this->authorizeClassroom($session);

        if ($session->isOpen()) {
            return to_route('cms.teacher.attendance.session', $session)
                ->withErrors(['correction' => __('You can edit this open session directly instead of requesting a correction.')]);
        }

        $record = AttendanceRecord::query()
            ->where('attendance_session_id', $session->getKey())
            ->where('student_id', $request->validated('student_id'))
            ->first();

        if (! $record) {
            return to_route('cms.teacher.attendance.session', $session)
                ->withErrors(['correction' => __('No attendance record found for that student.')]);
        }

        (new AttendanceService)->requestCorrection(
            $record,
            $request->validated('requested_status'),
            $request->validated('reason'),
            auth()->id()
        );

        ActivityLogger::log(
            'requested an attendance correction for '.($record->student?->full_name ?? 'student'),
            'attendance',
            $session->getKey()
        );

        return to_route('cms.teacher.attendance.session', $session)
            ->with('status', __('Correction requested. It will be reviewed by an administrator.'));
    }

    public function lessonPlans(): View
    {
        return view('portals.teacher.lesson-plans', [
            'lessonPlans' => LessonPlan::query()
                ->whereIn('class_subject_id', $this->myClassSubjectIds())
                ->with(['classSubject.subject'])
                ->latest('scheduled_date')
                ->get(),
        ]);
    }

    public function lessonPlansCreate(): View
    {
        return view('portals.teacher.lesson-plans-create', [
            'subjects' => $this->myClassSubjectQuery()->get(),
        ]);
    }

    public function lessonPlansStore(): RedirectResponse
    {
        $validated = request()->validate([
            'class_subject_id' => ['required', 'exists:class_subject,id', $this->myClassSubjectInRule()],
            'topic' => ['required', 'string', 'max:200'],
            'scheduled_date' => ['nullable', 'date'],
        ]);

        LessonPlan::create([
            'class_subject_id' => $validated['class_subject_id'],
            'teacher_id' => auth()->id(),
            ...request()->only([
                'week_number', 'unit', 'topic', 'objectives', 'materials',
                'activities', 'assessment', 'homework', 'status', 'scheduled_date',
            ]),
        ]);

        return to_route('cms.teacher.lesson-plans')->with('status', __('Lesson plan created.'));
    }

    public function lessonPlansEdit(LessonPlan $lessonPlan): View
    {
        $this->authorizeOwned($lessonPlan);

        return view('portals.teacher.lesson-plans-edit', [
            'lessonPlan' => $lessonPlan,
            'subjects' => $this->myClassSubjectQuery()->get(),
        ]);
    }

    public function lessonPlansUpdate(LessonPlan $lessonPlan): RedirectResponse
    {
        $this->authorizeOwned($lessonPlan);

        $validated = request()->validate([
            'class_subject_id' => ['required', 'exists:class_subject,id', $this->myClassSubjectInRule()],
            'topic' => ['required', 'string', 'max:200'],
            'scheduled_date' => ['nullable', 'date'],
        ]);

        $lessonPlan->update([
            'class_subject_id' => $validated['class_subject_id'],
            ...request()->only([
                'week_number', 'unit', 'topic', 'objectives', 'materials',
                'activities', 'assessment', 'homework', 'status', 'scheduled_date',
            ]),
        ]);

        return to_route('cms.teacher.lesson-plans')->with('status', __('Lesson plan updated.'));
    }

    public function lessonPlansDestroy(LessonPlan $lessonPlan): RedirectResponse
    {
        $this->authorizeOwned($lessonPlan);

        $lessonPlan->delete();

        return to_route('cms.teacher.lesson-plans')->with('status', __('Lesson plan deleted.'));
    }

    public function curriculum(): View
    {
        return view('portals.teacher.curriculum', [
            'curriculumUnits' => CurriculumUnit::query()
                ->whereIn('class_subject_id', $this->myClassSubjectIds())
                ->with(['classSubject.subject'])
                ->orderBy('position')
                ->get(),
            'subjects' => $this->myClassSubjectQuery()->get(),
        ]);
    }

    public function curriculumStore(): RedirectResponse
    {
        request()->validate([
            'class_subject_id' => ['required', 'exists:class_subject,id', $this->myClassSubjectInRule()],
            'title' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:1'],
            'total_lessons' => ['nullable', 'integer', 'min:1'],
            'covered_lessons' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'completed'])],
        ]);

        CurriculumUnit::create([
            'class_subject_id' => request('class_subject_id'),
            'teacher_id' => auth()->id(),
            'title' => request('title'),
            'description' => request('description'),
            'position' => request('position', 1),
            'total_lessons' => request('total_lessons', 1),
            'covered_lessons' => request('covered_lessons', 0),
            'status' => request('status', 'pending'),
            'started_at' => request('started_at'),
            'completed_at' => request('completed_at'),
        ]);

        return to_route('cms.teacher.curriculum')->with('status', __('Curriculum unit created.'));
    }

    public function curriculumUpdate(CurriculumUnit $curriculumUnit): RedirectResponse
    {
        $this->authorizeOwned($curriculumUnit);

        request()->validate([
            'title' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:1'],
            'total_lessons' => ['nullable', 'integer', 'min:1'],
            'covered_lessons' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ]);

        $curriculumUnit->update(request()->only([
            'title', 'description', 'position', 'total_lessons', 'covered_lessons',
            'status', 'started_at', 'completed_at',
        ]));

        return to_route('cms.teacher.curriculum')->with('status', __('Curriculum unit updated.'));
    }

    public function homework(): View
    {
        return view('portals.teacher.homework', [
            'assignments' => HomeworkAssignment::query()
                ->whereIn('class_subject_id', $this->myClassSubjectIds())
                ->with(['classSubject.subject'])
                ->latest('due_on')
                ->get(),
        ]);
    }

    public function homeworkCreate(): View
    {
        return view('portals.teacher.homework-create', [
            'subjects' => $this->myClassSubjectQuery()->get(),
        ]);
    }

    public function homeworkStore(): RedirectResponse
    {
        request()->validate([
            'class_subject_id' => ['required', 'exists:class_subject,id', $this->myClassSubjectInRule()],
            'title' => ['required', 'string', 'max:200'],
            'assigned_on' => ['required', 'date'],
            'due_on' => ['required', 'date', 'after_or_equal:assigned_on'],
        ]);

        HomeworkAssignment::create([
            'class_subject_id' => request('class_subject_id'),
            'teacher_id' => auth()->id(),
            'title' => request('title'),
            'instructions' => request('instructions'),
            'assigned_on' => request('assigned_on'),
            'due_on' => request('due_on'),
            'max_marks' => request('max_marks'),
            'visibility' => request('visibility', 'class'),
            'status' => request('status', 'draft'),
        ]);

        return to_route('cms.teacher.homework')->with('status', __('Homework assignment created.'));
    }

    public function homeworkShow(HomeworkAssignment $homeworkAssignment): View
    {
        $this->authorizeOwned($homeworkAssignment);

        return view('portals.teacher.homework-show', [
            'assignment' => $homeworkAssignment->load(['classSubject.subject']),
            'submissions' => $homeworkAssignment->submissions()->with('student')->get(),
        ]);
    }

    public function homeworkPublish(HomeworkAssignment $homeworkAssignment): RedirectResponse
    {
        $this->authorizeOwned($homeworkAssignment);

        $homeworkAssignment->update(['status' => 'published']);

        return to_route('cms.teacher.homework')->with('status', __('Homework assignment published.'));
    }

    public function homeworkEdit(HomeworkAssignment $homeworkAssignment): View
    {
        $this->authorizeOwned($homeworkAssignment);

        return view('portals.teacher.homework-edit', [
            'assignment' => $homeworkAssignment,
            'subjects' => $this->myClassSubjectQuery()->get(),
        ]);
    }

    public function homeworkUpdate(HomeworkAssignment $homeworkAssignment): RedirectResponse
    {
        $this->authorizeOwned($homeworkAssignment);

        $validated = request()->validate([
            'class_subject_id' => ['required', 'exists:class_subject,id', $this->myClassSubjectInRule()],
            'title' => ['required', 'string', 'max:200'],
            'assigned_on' => ['required', 'date'],
            'due_on' => ['required', 'date', 'after_or_equal:assigned_on'],
        ]);

        $homeworkAssignment->update([
            'class_subject_id' => $validated['class_subject_id'],
            ...request()->only([
                'title', 'instructions', 'assigned_on', 'due_on',
                'max_marks', 'visibility', 'status',
            ]),
        ]);

        return to_route('cms.teacher.homework')->with('status', __('Homework assignment updated.'));
    }

    public function homeworkDestroy(HomeworkAssignment $homeworkAssignment): RedirectResponse
    {
        $this->authorizeOwned($homeworkAssignment);

        $homeworkAssignment->delete();

        return to_route('cms.teacher.homework')->with('status', __('Homework assignment deleted.'));
    }

    public function assessments(): View
    {
        return view('portals.teacher.assessments', [
            'assessments' => ClassroomAssessment::query()
                ->whereIn('class_subject_id', $this->myClassSubjectIds())
                ->with(['classSubject.subject', 'classRoom.gradeLevel'])
                ->withCount('results')
                ->latest()
                ->get(),
            'subjects' => $this->myClassSubjectQuery()->get(),
        ]);
    }

    public function assessmentsCreate(): View
    {
        return view('portals.teacher.assessments-create', [
            'subjects' => $this->myClassSubjectQuery()->get(),
            'classRooms' => ClassRoom::query()
                ->whereKey($this->myClassRoomIds())
                ->with('gradeLevel')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function assessmentsStore(): RedirectResponse
    {
        request()->validate([
            'class_subject_id' => ['required', 'exists:class_subject,id', $this->myClassSubjectInRule()],
            'class_room_id' => ['required', 'exists:class_rooms,id', $this->myClassRoomInRule()],
            'title' => ['required', 'string', 'max:200'],
        ]);

        ClassroomAssessment::create([
            'class_subject_id' => request('class_subject_id'),
            'class_room_id' => request('class_room_id'),
            'teacher_id' => auth()->id(),
            'title' => request('title'),
            'type' => request('type', 'quiz'),
            'status' => request('status', 'draft'),
            'total_marks' => request('total_marks', 100),
            'assessment_date' => request('assessment_date'),
            'room' => request('room'),
            'instructions' => request('instructions'),
        ]);

        return to_route('cms.teacher.assessments')->with('status', __('Assessment created.'));
    }

    public function assessmentsGrades(ClassroomAssessment $classroomAssessment): View
    {
        $this->authorizeOwned($classroomAssessment);

        return view('portals.teacher.assessments-grades', [
            'assessment' => $classroomAssessment->load(['classSubject.subject', 'classRoom.gradeLevel']),
            'students' => $classroomAssessment->classRoom?->students()->orderBy('student_number')->get() ?? collect(),
            'results' => $classroomAssessment->results()->get(),
        ]);
    }

    public function assessmentsGradesStore(ClassroomAssessment $classroomAssessment): RedirectResponse
    {
        $this->authorizeOwned($classroomAssessment);

        $rosterIds = $classroomAssessment->classRoom?->students()->pluck('id') ?? collect();

        $validated = request()->validate([
            'student_id' => ['required', 'array', 'min:1'],
            'student_id.*' => ['required', 'in:'.$rosterIds->implode(',')],
            'marks_obtained.*' => ['nullable', 'numeric', 'min:0', 'max:'.(int) $classroomAssessment->total_marks],
        ]);

        foreach ($validated['student_id'] as $studentId) {
            $marks = $validated['marks_obtained'][$studentId] ?? null;

            if ($marks === null || $marks === '') {
                continue;
            }

            AssessmentResult::updateOrCreate(
                ['classroom_assessment_id' => $classroomAssessment->getKey(), 'student_id' => $studentId],
                [
                    'marks_obtained' => $marks,
                    'status' => 'entered',
                    'entered_by_id' => auth()->id(),
                ]
            );
        }

        return to_route('cms.teacher.assessments.grades', $classroomAssessment)->with('status', __('Grades recorded.'));
    }

    public function behavior(): View
    {
        $subjectIds = $this->myClassSubjectIds();

        return view('portals.teacher.behavior', [
            'students' => Student::query()
                ->whereIn('class_room_id', $this->myClassRoomIds())
                ->with('classRoom.gradeLevel')
                ->get(),
            'notes' => BehaviorNote::query()
                ->where(function (Builder $query) use ($subjectIds) {
                    $query->whereIn('class_subject_id', $subjectIds)
                        ->orWhere('teacher_id', auth()->id());
                })
                ->with(['student', 'classSubject.subject'])
                ->latest('recorded_on')
                ->get(),
        ]);
    }

    public function behaviorStore(): RedirectResponse
    {
        $studentIds = Student::query()
            ->whereIn('class_room_id', $this->myClassRoomIds())
            ->pluck('id');

        request()->validate([
            'student_id' => ['required', 'exists:students,id', 'in:'.$studentIds->implode(',')],
            'recorded_on' => ['required', 'date'],
            'note' => ['required', 'string'],
        ]);

        BehaviorNote::create([
            'student_id' => request('student_id'),
            'teacher_id' => auth()->id(),
            'class_subject_id' => request('class_subject_id'),
            'type' => request('type', 'observation'),
            'severity' => request('severity', 'info'),
            'recorded_on' => request('recorded_on'),
            'note' => request('note'),
            'action_taken' => request('action_taken'),
            'visibility' => request('visibility', 'teacher'),
        ]);

        return to_route('cms.teacher.behavior')->with('status', __('Behavior note recorded.'));
    }

    public function behaviorEdit(BehaviorNote $behaviorNote): View
    {
        $this->authorizeOwned($behaviorNote);

        return view('portals.teacher.behavior-edit', [
            'note' => $behaviorNote,
            'students' => Student::query()
                ->whereIn('class_room_id', $this->myClassRoomIds())
                ->with('classRoom.gradeLevel')
                ->get(),
        ]);
    }

    public function behaviorUpdate(BehaviorNote $behaviorNote): RedirectResponse
    {
        $this->authorizeOwned($behaviorNote);

        $studentIds = Student::query()
            ->whereIn('class_room_id', $this->myClassRoomIds())
            ->pluck('id');

        $validated = request()->validate([
            'student_id' => ['required', 'exists:students,id', 'in:'.$studentIds->implode(',')],
            'recorded_on' => ['required', 'date'],
            'note' => ['required', 'string'],
            'type' => ['nullable', Rule::in(['observation', 'positive', 'concern'])],
            'severity' => ['nullable', Rule::in(['info', 'warning', 'serious'])],
            'visibility' => ['nullable', Rule::in(['teacher', 'subject', 'school'])],
        ]);

        $behaviorNote->update([
            ...$validated,
            'class_subject_id' => request('class_subject_id'),
            'action_taken' => request('action_taken'),
        ]);

        return to_route('cms.teacher.behavior')->with('status', __('Behavior note updated.'));
    }

    public function behaviorDestroy(BehaviorNote $behaviorNote): RedirectResponse
    {
        $this->authorizeOwned($behaviorNote);

        $behaviorNote->delete();

        return to_route('cms.teacher.behavior')->with('status', __('Behavior note deleted.'));
    }

    public function progress(): View
    {
        $subjectIds = $this->myClassSubjectIds();
        $assessmentIds = ClassroomAssessment::query()->whereIn('class_subject_id', $subjectIds)->pluck('id');

        $students = Student::query()
            ->whereIn('class_room_id', $this->myClassRoomIds())
            ->with('classRoom.gradeLevel')
            ->get();

        $assessmentResults = AssessmentResult::query()
            ->whereIn('classroom_assessment_id', $assessmentIds)
            ->with('assessment.classSubject.subject')
            ->get();

        $rows = $students->map(function (Student $student) use ($assessmentResults) {
            $results = $assessmentResults->where('student_id', $student->getKey());

            return [
                'student' => $student,
                'assessmentCount' => $results->count(),
                'assessmentAverage' => $results->isEmpty()
                    ? null
                    : round($results->avg('marks_obtained'), 1),
            ];
        });

        return view('portals.teacher.progress', [
            'rows' => $rows,
            'assessments' => ClassroomAssessment::query()
                ->whereIn('class_subject_id', $subjectIds)
                ->with(['classSubject.subject', 'classRoom.gradeLevel', 'results'])
                ->latest()
                ->get(),
        ]);
    }

    public function homeroom(): View
    {
        $homeroom = $this->myClassSubjectQuery()->homeroom()->with(['classRoom.gradeLevel'])->first();

        $students = $homeroom
            ? Student::query()->where('class_room_id', $homeroom->class_room_id)->with('classRoom.gradeLevel')->get()
            : collect();

        $sessionCount = $homeroom
            ? AttendanceSession::query()->where('class_room_id', $homeroom->class_room_id)->count()
            : 0;

        return view('portals.teacher.homeroom', [
            'homeroom' => $homeroom,
            'students' => $students,
            'sessionCount' => $sessionCount,
        ]);
    }

    public function messages(): View
    {
        return view('portals.teacher.messages', [
            'recipients' => AccountUser::query()
                ->active()
                ->whereKeyNot(auth()->id())
                ->with('roles')
                ->orderBy('first_name')
                ->get(),
            'inbox' => TeacherMessage::query()
                ->where('recipient_id', auth()->id())
                ->with(['sender', 'recipient'])
                ->latest()
                ->get(),
            'outbox' => TeacherMessage::query()
                ->where('sender_id', auth()->id())
                ->with(['sender', 'recipient'])
                ->latest()
                ->get(),
        ]);
    }

    public function messagesStore(): RedirectResponse
    {
        $validated = request()->validate([
            'recipient_uid' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string'],
        ]);

        abort_if((string) $validated['recipient_uid'] === (string) auth()->id(), 403);

        $recipient = AccountUser::findOrFail($validated['recipient_uid']);

        TeacherMessage::create([
            'sender_id' => auth()->id(),
            'recipient_type' => $recipient->roles()->first()?->name ?? 'staff',
            'recipient_id' => $validated['recipient_uid'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'message_type' => 'individual',
            'status' => 'sent',
        ]);

        return to_route('cms.teacher.messages')->with('status', __('Message sent.'));
    }

    public function resources(): View
    {
        return view('portals.teacher.resources', [
            'resources' => TeachingResource::query()
                ->where('teacher_id', auth()->id())
                ->with(['classSubject.subject'])
                ->latest()
                ->get(),
            'subjects' => $this->myClassSubjectQuery()->get(),
        ]);
    }

    public function resourcesStore(): RedirectResponse
    {
        request()->validate([
            'class_subject_id' => ['required', 'exists:class_subject,id', $this->myClassSubjectInRule()],
            'title' => ['required', 'string', 'max:200'],
        ]);

        TeachingResource::create([
            'teacher_id' => auth()->id(),
            'class_subject_id' => request('class_subject_id'),
            'title' => request('title'),
            'type' => request('type', 'document'),
            'external_url' => request('external_url'),
            'unit' => request('unit'),
            'visibility' => request('visibility', 'teacher'),
            'description' => request('description'),
        ]);

        return to_route('cms.teacher.resources')->with('status', __('Resource added.'));
    }

    public function resourceEdit(TeachingResource $resource): View
    {
        $this->authorizeOwned($resource);

        return view('portals.teacher.resources-edit', [
            'resource' => $resource,
            'subjects' => $this->myClassSubjectQuery()->get(),
        ]);
    }

    public function resourceUpdate(TeachingResource $resource): RedirectResponse
    {
        $this->authorizeOwned($resource);

        request()->validate([
            'class_subject_id' => ['required', 'exists:class_subject,id', $this->myClassSubjectInRule()],
            'title' => ['required', 'string', 'max:200'],
            'type' => ['nullable', Rule::in(['document', 'worksheet', 'presentation', 'video', 'link', 'image'])],
            'external_url' => ['nullable', 'url', 'max:2048'],
            'visibility' => ['nullable', Rule::in(['teacher', 'subject', 'school'])],
            'unit' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $resource->update(request()->only([
            'class_subject_id', 'title', 'type', 'external_url', 'unit', 'visibility', 'description',
        ]));

        return to_route('cms.teacher.resources')->with('status', __('Resource updated.'));
    }

    public function resourceDestroy(TeachingResource $resource): RedirectResponse
    {
        $this->authorizeOwned($resource);

        $resource->delete();

        return to_route('cms.teacher.resources')->with('status', __('Resource deleted.'));
    }

    public function reports(): View
    {
        $subjectIds = $this->myClassSubjectIds();
        $classRoomIds = $this->myClassRoomIds();

        $assessments = ClassroomAssessment::query()
            ->whereIn('class_subject_id', $subjectIds)
            ->with(['classSubject.subject', 'classRoom.gradeLevel', 'results'])
            ->latest()
            ->get();

        $assessmentRows = $assessments->map(function (ClassroomAssessment $assessment) {
            $graded = $assessment->results->filter(fn ($result) => $result->marks_obtained !== null);
            $marks = $graded->pluck('marks_obtained');

            $passRate = $graded->isEmpty()
                ? null
                : round(
                    $graded->filter(
                        fn ($result) => $result->marks_obtained / $assessment->total_marks >= 0.5
                    )->count() / $graded->count() * 100,
                    1
                );

            return [
                'assessment' => $assessment,
                'students' => $graded->count(),
                'average' => $marks->isEmpty() ? null : round($marks->avg(), 1),
                'highest' => $marks->isEmpty() ? null : $marks->max(),
                'lowest' => $marks->isEmpty() ? null : $marks->min(),
                'passRate' => $passRate,
            ];
        });

        $students = Student::query()
            ->whereIn('class_room_id', $classRoomIds)
            ->with('classRoom.gradeLevel')
            ->get();

        $assessmentResults = AssessmentResult::query()
            ->whereIn('classroom_assessment_id', $assessments->pluck('id'))
            ->get();

        $studentRows = $students->map(function (Student $student) use ($assessmentResults) {
            $results = $assessmentResults->where('student_id', $student->getKey());

            return [
                'student' => $student,
                'assessmentsTaken' => $results->count(),
                'average' => $results->isEmpty() ? null : round($results->avg('marks_obtained'), 1),
            ];
        });

        $attendanceRows = ClassRoom::query()
            ->whereKey($classRoomIds)
            ->with('gradeLevel')
            ->get()
            ->map(function (ClassRoom $classRoom) {
                $records = AttendanceRecord::query()
                    ->whereIn(
                        'attendance_session_id',
                        AttendanceSession::query()->where('class_room_id', $classRoom->getKey())->pluck('id')
                    )
                    ->get();

                $total = $records->count();

                $present = $records->whereIn('status', [
                    AttendanceStatus::Present->value,
                    AttendanceStatus::Late->value,
                    AttendanceStatus::Excused->value,
                ])->count();

                return [
                    'classRoom' => $classRoom,
                    'total' => $total,
                    'rate' => $total === 0 ? null : round($present / $total * 100, 1),
                ];
            });

        return view('portals.teacher.reports', [
            'assessmentRows' => $assessmentRows,
            'studentRows' => $studentRows,
            'attendanceRows' => $attendanceRows,
        ]);
    }

    public function meetings(): View
    {
        return view('portals.teacher.meetings', [
            'meetings' => MeetingRequest::query()
                ->where('teacher_id', auth()->id())
                ->with(['guardian', 'student.classRoom.gradeLevel'])
                ->latest()
                ->get(),
        ]);
    }

    public function meetingsReview(MeetingRequest $meetingRequest): RedirectResponse
    {
        abort_unless((string) $meetingRequest->teacher_id === (string) auth()->id(), 403);

        $validated = request()->validate([
            'status' => ['required', Rule::in([
                MeetingRequestStatus::Confirmed->value,
                MeetingRequestStatus::Completed->value,
                MeetingRequestStatus::Cancelled->value,
            ])],
            'staff_note' => ['nullable', 'string', 'max:190'],
        ]);

        $meetingRequest->update([
            'status' => $validated['status'],
            'staff_note' => $validated['staff_note'] ?? null,
            'reviewed_at' => now(),
        ]);

        $guardianUserId = $meetingRequest->guardian?->user_id;

        if ($guardianUserId) {
            app(NotificationService::class)->sendToUser($guardianUserId, [
                'type' => 'meeting',
                'category' => 'system',
                'priority' => 'medium',
                'icon' => 'calendar',
                'title' => __('Meeting :status', ['status' => $validated['status']]),
                'body' => __('The :date meeting for :student is now :status.', [
                    'date' => $meetingRequest->preferred_date?->format('d M Y'),
                    'student' => $meetingRequest->student?->full_name,
                    'status' => $validated['status'],
                ]),
                'redirect_url' => route('cms.parent.meetings'),
            ]);
        }

        return to_route('cms.teacher.meetings')
            ->with('status', __('Meeting marked as :status.', ['status' => $validated['status']]));
    }

    public function messagesReply(TeacherMessage $message): RedirectResponse
    {
        abort_unless((string) $message->recipient_id === (string) auth()->id(), 403);

        $validated = request()->validate([
            'body' => ['required', 'string'],
        ]);

        TeacherMessage::create([
            'sender_id' => auth()->id(),
            'recipient_type' => 'guardian',
            'recipient_id' => $message->sender_id,
            'subject' => $message->subject,
            'body' => $validated['body'],
            'message_type' => 'individual',
            'status' => 'sent',
            'reply_to_id' => $message->getKey(),
        ]);

        $guardianUserId = $message->sender_id;

        if ($guardianUserId) {
            app(NotificationService::class)->sendToUser($guardianUserId, [
                'type' => 'message',
                'category' => 'system',
                'priority' => 'low',
                'icon' => 'mail',
                'title' => __('New reply'),
                'body' => __('You have a new reply about ":subject".', ['subject' => $message->subject]),
                'redirect_url' => route('cms.parent.messages'),
            ]);
        }

        return to_route('cms.teacher.messages')->with('status', __('Reply sent.'));
    }
}

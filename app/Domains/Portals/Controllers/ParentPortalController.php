<?php

namespace App\Domains\Portals\Controllers;

use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Cms\Models\Event;
use App\Domains\Cms\Models\NewsItem;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\ParentPortal\Models\AbsenceRequest;
use App\Domains\ParentPortal\Models\MeetingRequest;
use App\Domains\ParentPortal\Models\ParentRequest;
use App\Domains\Students\Models\ParentProfile;
use App\Domains\Students\Models\Student;
use App\Domains\Students\Models\StudentDocument;
use App\Domains\TeacherPortal\Models\AssessmentResult;
use App\Domains\TeacherPortal\Models\ClassroomAssessment;
use App\Domains\TeacherPortal\Models\HomeworkAssignment;
use App\Domains\TeacherPortal\Models\TeacherMessage;
use App\Http\Controllers\Controller;
use App\Support\Enums\AbsenceRequestStatus;
use App\Support\Enums\ExamStatus;
use App\Support\Enums\MeetingRequestStatus;
use App\Support\Enums\MeetingRequestType;
use App\Support\Enums\ParentRequestStatus;
use App\Support\Enums\ParentRequestType;
use App\Support\Enums\RoleName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentPortalController extends Controller
{
    private function currentParent(): ?ParentProfile
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        return $user?->parent;
    }

    private function wards(): Collection
    {
        $guardian = $this->currentParent();

        return $guardian?->students()
            ->with(['gradeLevel', 'classRoom.gradeLevel'])
            ->orderBy('first_name')
            ->get() ?? collect();
    }

    private function authorizeWard(Student $student): void
    {
        $owns = $this->currentParent()?->students()
            ->whereKey($student->getKey())
            ->exists();

        abort_unless((bool) $owns, 404);
    }

    private function selectedWard(?Collection $wards = null): ?Student
    {
        $wards ??= $this->wards();

        if ($wards->isEmpty()) {
            return null;
        }

        $requested = $wards->firstWhere('id', session('parent_ward_id'));

        return $requested ?? $wards->first();
    }

    private function viewData(?Student $ward = null): array
    {
        return [
            'guardian' => $this->currentParent(),
            'wards' => $this->wards(),
            'ward' => $ward,
        ];
    }

    private function notifyStaff(array $payload): void
    {
        app(NotificationService::class)->sendToRoles([
            RoleName::Registrar->value,
            RoleName::SchoolAdmin->value,
            RoleName::Principal->value,
            RoleName::SuperAdmin->value,
        ], $payload);
    }

    /* ---------------------------------- Dashboard --------------------------------- */

    public function dashboard(): View
    {
        $guardian = $this->currentParent();
        $wards = $this->wards();
        $ward = $this->selectedWard($wards);

        $attendance = $ward ? $this->attendanceSummary($ward) : null;
        $todayStatus = $ward ? $this->todayAttendanceStatus($ward) : null;

        $homework = $ward && $guardian?->canAccess('academics', $ward)
            ? $this->pendingHomework($ward)->take(5)
            : collect();

        $recentGrades = $ward && $guardian?->canAccess('academics', $ward)
            ? $this->publishedGrades($ward)->take(5)
            : collect();

        return view('portals.parent.dashboard', [
            ...$this->viewData($ward),
            'attendance' => $attendance,
            'todayStatus' => $todayStatus,
            'homework' => $homework,
            'recentGrades' => $recentGrades,
            'announcements' => $this->latestAnnouncements()->take(3),
            'upcomingEvents' => $this->upcomingEvents()->take(3),
            'unreadNotifications' => Notification::where('user_id', auth()->id())->unread()->count(),
        ]);
    }

    /* --------------------------------- My children -------------------------------- */

    public function wardsIndex(): View
    {
        $wards = $this->wards();
        $ward = $this->selectedWard($wards);

        return view('portals.parent.wards', [...$this->viewData($ward)]);
    }

    public function switchWard(Request $request): RedirectResponse
    {
        $guardian = $this->currentParent();

        $validated = $request->validate([
            'student' => ['required', 'exists:students,id'],
        ]);

        abort_unless((bool) $guardian?->students()->whereKey($validated['student'])->exists(), 403);

        session(['parent_ward_id' => $validated['student']]);

        return to_route('cms.parent.dashboard');
    }

    public function wardShow(Student $student): View
    {
        $this->authorizeWard($student);
        $guardian = $this->currentParent();
        $wards = $this->wards();

        $attendance = $this->attendanceSummary($student);

        return view('portals.parent.profile', [
            ...$this->viewData($student),
            'attendance' => $attendance,
            'recentGrades' => $guardian?->canAccess('academics', $student)
                ? $this->publishedGrades($student)->take(5)
                : collect(),
            'recentAttendance' => AttendanceRecord::query()
                ->with('session')
                ->where('student_id', $student->getKey())
                ->latest('created_at')
                ->limit(10)
                ->get(),
        ]);
    }

    /* ---------------------------------- Attendance -------------------------------- */

    private function attendanceSummary(Student $ward): array
    {
        $records = AttendanceRecord::query()->where('student_id', $ward->getKey())->get();

        return [
            'total' => $records->count(),
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'late' => $records->where('status', 'late')->count(),
            'excused' => $records->where('status', 'excused')->count(),
            'percentage' => $ward->attendancePercentage(),
        ];
    }

    private function todayAttendanceStatus(Student $ward): ?string
    {
        return AttendanceRecord::query()
            ->where('student_id', $ward->getKey())
            ->whereHas('session', fn (Builder $query) => $query->whereDate('date', today()))
            ->value('status');
    }

    public function attendance(Request $request): View
    {
        $ward = $this->selectedWard();
        abort_unless(! $ward || $this->currentParent()?->canAccess('attendance', $ward), 403);

        if (! $ward) {
            return view('portals.parent.attendance', [...$this->viewData(null), 'records' => collect(), 'month' => null]);
        }

        $month = $request->query('month') ?: now()->format('Y-m');
        [$year, $monthNumber] = array_pad(explode('-', $month), 2, null);
        $monthNumber ??= now()->format('m');

        $records = AttendanceRecord::query()
            ->where('student_id', $ward->getKey())
            ->whereHas('session', function (Builder $query) use ($year, $monthNumber) {
                $query->whereYear('date', $year)->whereMonth('date', $monthNumber);
            })
            ->with('session')
            ->get();

        $calendar = $records->mapWithKeys(function (AttendanceRecord $record) {
            return [$record->session->date->format('Y-m-d') => $record->status];
        });

        return view('portals.parent.attendance', [
            ...$this->viewData($ward),
            'records' => $records,
            'calendar' => $calendar,
            'month' => "$year-$monthNumber",
            'summary' => $this->attendanceSummary($ward),
        ]);
    }

    /* -------------------------------- Absence requests ----------------------------- */

    public function absenceRequests(): View
    {
        $ward = $this->selectedWard();
        $guardian = $this->currentParent();

        return view('portals.parent.absence-requests', [
            ...$this->viewData($ward),
            'requests' => $guardian
                ? AbsenceRequest::query()->where('guardian_id', $guardian->getKey())->with('student')->latest()->get()
                : collect(),
        ]);
    }

    public function absenceRequestsStore(): RedirectResponse
    {
        $guardian = $this->currentParent();
        abort_unless((bool) $guardian, 403);

        $validated = request()->validate([
            'student' => ['required', 'exists:students,id'],
            'absence_date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['required', 'string', 'max:190'],
        ]);

        abort_unless((bool) $guardian->students()->whereKey($validated['student'])->exists(), 403);
        abort_unless($guardian->canAccess('attendance', $guardian->students()->find($validated['student'])), 403);

        $data = $validated;
        $data['student_id'] = $data['student'];
        unset($data['student']);

        AbsenceRequest::create([
            ...$data,
            'guardian_id' => $guardian->getKey(),
            'status' => AbsenceRequestStatus::Submitted->value,
            'submitted_at' => now(),
        ]);

        if ($homeroom = $guardian->students()->find($validated['student'])?->homeroomTeacher()) {
            app(NotificationService::class)->sendToUser($homeroom->getKey(), [
                'type' => 'absence',
                'category' => 'attendance',
                'priority' => 'medium',
                'icon' => 'clipboard-check',
                'title' => __('Absence notice submitted'),
                'body' => __('A guardian reported that their child will be absent on :date.', [
                    'date' => Carbon::parse($validated['absence_date'])->format('d M Y'),
                ]),
                'redirect_url' => route('cms.teacher.homeroom'),
            ]);
        }

        return to_route('cms.parent.absence-requests')->with('status', __('Absence explained. The school will review it.'));
    }

    /* ---------------------------------- Homework ---------------------------------- */

    private function pendingHomework(Student $ward): Collection
    {
        return HomeworkAssignment::query()
            ->where('status', 'published')
            ->whereHas('classSubject', fn (Builder $query) => $query->where('class_room_id', $ward->class_room_id))
            ->with(['classSubject.subject', 'submissions'])
            ->orderByDesc('due_on')
            ->get();
    }

    public function homework(): View
    {
        $ward = $this->selectedWard();
        abort_unless(! $ward || $this->currentParent()?->canAccess('academics', $ward), 403);

        return view('portals.parent.homework', [
            ...$this->viewData($ward),
            'assignments' => $ward ? $this->pendingHomework($ward) : collect(),
        ]);
    }

    /* ----------------------------------- Academics -------------------------------- */

    private function publishedGrades(Student $ward): Collection
    {
        $assessmentIds = ClassroomAssessment::query()
            ->whereIn('class_subject_id', ClassSubject::query()->where('class_room_id', $ward->class_room_id)->pluck('id'))
            ->where('status', '!=', 'draft')
            ->pluck('id');

        $assessmentResults = AssessmentResult::query()
            ->where('student_id', $ward->getKey())
            ->where('status', 'published')
            ->whereIn('classroom_assessment_id', $assessmentIds)
            ->with(['assessment.classSubject.subject'])
            ->latest('updated_at')
            ->get();

        $examResults = ExamResult::query()
            ->where('student_id', $ward->getKey())
            ->whereHas('examSubject.exam', fn (Builder $query) => $query->whereIn('status', [
                ExamStatus::Published->value,
                ExamStatus::Completed->value,
            ]))
            ->with(['examSubject.subject', 'examSubject.exam'])
            ->latest('created_at')
            ->get();

        return $assessmentResults->concat($examResults);
    }

    public function academics(): View
    {
        $ward = $this->selectedWard();
        abort_unless(! $ward || $this->currentParent()?->canAccess('academics', $ward), 403);

        $grades = $ward ? $this->publishedGrades($ward) : collect();

        $bySubject = $grades->groupBy(function ($grade) {
            $subject = $grade->assessment?->classSubject?->subject ?? $grade->examSubject?->subject;

            return (string) $subject?->name;
        });

        return view('portals.parent.academics', [
            ...$this->viewData($ward),
            'grades' => $grades,
            'bySubject' => $bySubject,
        ]);
    }

    public function reportCard(): View
    {
        $ward = $this->selectedWard();
        abort_unless(! $ward || $this->currentParent()?->canAccess('academics', $ward), 403);

        $grades = $ward ? $this->publishedGrades($ward) : collect();

        $subjects = $grades->groupBy(function ($grade) {
            return (string) ($grade->assessment?->classSubject?->subject?->name
                ?? $grade->examSubject?->subject?->name ?? '');
        });

        $totalObtained = $grades->sum(fn ($grade) => (float) ($grade->marks_obtained ?? 0));
        $totalMax = $grades->sum(fn ($grade) => (float) ($grade->assessment?->total_marks
            ?? $grade->examSubject?->max_marks ?? 0));

        $totals = [
            'obtained' => round($totalObtained, 2),
            'max' => round($totalMax, 2),
            'average' => $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : null,
        ];

        return view('portals.parent.report-card', [
            ...$this->viewData($ward),
            'subjects' => $subjects,
            'totals' => $totals,
            'attendance' => $ward ? $this->attendanceSummary($ward) : null,
        ]);
    }

    /* ----------------------------------- Calendar ---------------------------------- */

    public function calendar(): View
    {
        $ward = $this->selectedWard();

        $events = $this->upcomingEvents(30);

        $homework = $ward && $this->currentParent()?->canAccess('academics', $ward)
            ? $this->pendingHomework($ward)->where('due_on', '>=', today())->sortBy('due_on')->take(10)
            : collect();

        $assessments = $ward && $this->currentParent()?->canAccess('academics', $ward)
            ? ClassroomAssessment::query()
                ->where('status', 'published')
                ->whereNotNull('assessment_date')
                ->whereIn('class_subject_id', ClassSubject::query()->where('class_room_id', $ward->class_room_id)->pluck('id'))
                ->with(['classSubject.subject'])
                ->orderBy('assessment_date')
                ->get()
            : collect();

        $meetings = $ward && $this->currentParent()?->canAccess('messages', $ward)
            ? MeetingRequest::query()->where('guardian_id', $this->currentParent()->getKey())->where('status', 'confirmed')->get()
            : collect();

        return view('portals.parent.calendar', [
            ...$this->viewData($ward),
            'entries' => collect()
                ->concat($events->map(fn (Event $event) => [
                    'date' => $event->starts_at,
                    'title' => $event->title,
                    'type' => 'event',
                    'context' => $event->location,
                ]))
                ->concat($homework->map(fn ($assignment) => [
                    'date' => $assignment->due_on,
                    'title' => $assignment->title,
                    'type' => 'homework',
                    'context' => $assignment->classSubject?->subject?->name,
                ]))
                ->concat($assessments->map(fn ($assessment) => [
                    'date' => $assessment->assessment_date,
                    'title' => $assessment->title,
                    'type' => 'assessment',
                    'context' => $assessment->classSubject?->subject?->name,
                ]))
                ->concat($meetings->map(fn (MeetingRequest $meeting) => [
                    'date' => $meeting->preferred_date,
                    'title' => __('Parent-teacher meeting'),
                    'type' => 'meeting',
                    'context' => $meeting->teacher?->full_name,
                ]))
                ->sortBy('date')
                ->values(),
        ]);
    }

    /* --------------------------------- Announcements ------------------------------- */

    private function latestAnnouncements(int $limit = 6): Collection
    {
        return NewsItem::query()
            ->published()
            ->whereIn('category', ['announcements', 'academic', 'holidays', 'emergency', 'sports', 'competitions'])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    private function upcomingEvents(int $days = 30): Collection
    {
        return Event::query()
            ->where('published', true)
            ->whereNull('deleted_at')
            ->where('starts_at', '>=', now())
            ->where('starts_at', '<=', now()->addDays($days))
            ->orderBy('starts_at')
            ->get();
    }

    public function announcements(): View
    {
        return view('portals.parent.announcements', [
            ...$this->viewData(),
            'items' => NewsItem::query()
                ->published()
                ->whereIn('category', ['announcements', 'academic', 'holidays', 'emergency', 'sports', 'competitions'])
                ->orderByDesc('published_at')
                ->get(),
        ]);
    }

    /* ----------------------------------- Messages ---------------------------------- */

    public function messages(): View
    {
        $ward = $this->selectedWard();
        $guardian = $this->currentParent();

        $conversations = TeacherMessage::query()
            ->where(function (Builder $query) {
                $query->where('sender_id', auth()->id())
                    ->orWhere(function (Builder $sub) {
                        $sub->where('recipient_type', 'guardian')
                            ->where('recipient_id', auth()->id());
                    });
            })
            ->with(['sender', 'recipient', 'replies.sender'])
            ->latest()
            ->get();

        return view('portals.parent.messages', [
            ...$this->viewData($ward),
            'conversations' => $conversations,
            'recipients' => $guardian ? $this->authorizedRecipients($guardian) : collect(),
        ]);
    }

    private function authorizedRecipients(ParentProfile $guardian): Collection
    {
        $classRoomIds = $guardian->students()->pluck('class_room_id')->filter()->unique()->values();

        $teacherIds = ClassSubject::query()
            ->whereIn('class_room_id', $classRoomIds)
            ->whereNotNull('teacher_id')
            ->pluck('teacher_id')
            ->unique()
            ->values();

        return User::query()
            ->active()
            ->whereKeyNot(auth()->id())
            ->where(function (Builder $query) use ($teacherIds) {
                $query->whereIn('id', $teacherIds)
                    ->orWhereHas('roles', fn (Builder $roles) => $roles->whereIn('name', [
                        RoleName::Registrar->value,
                        RoleName::SchoolAdmin->value,
                        RoleName::Principal->value,
                    ]));
            })
            ->with('roles')
            ->orderBy('first_name')
            ->get();
    }

    public function messagesStore(): RedirectResponse
    {
        $guardian = $this->currentParent();
        abort_unless((bool) $guardian, 403);

        $validated = request()->validate([
            'recipient_uid' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string'],
            'reply_to_id' => ['nullable', 'exists:teacher_messages,id'],
        ]);

        abort_unless($this->authorizedRecipients($guardian)->contains('id', $validated['recipient_uid']), 403);

        $recipient = User::findOrFail($validated['recipient_uid']);

        TeacherMessage::create([
            'sender_id' => auth()->id(),
            'recipient_type' => 'teacher',
            'recipient_id' => $validated['recipient_uid'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'message_type' => 'individual',
            'status' => 'sent',
            'reply_to_id' => $validated['reply_to_id'] ?? null,
        ]);

        app(NotificationService::class)->sendToUser($recipient->getKey(), [
            'type' => 'message',
            'category' => 'system',
            'priority' => 'low',
            'icon' => 'mail',
            'title' => __('New parent message'),
            'body' => __(':guardian sent you a message: :subject.', [
                'guardian' => $guardian->full_name,
                'subject' => $validated['subject'],
            ]),
            'redirect_url' => route('cms.teacher.messages'),
        ]);

        return to_route('cms.parent.messages')->with('status', __('Message sent.'));
    }

    /* -------------------------------- Meeting requests ----------------------------- */

    public function meetingRequests(): View
    {
        $ward = $this->selectedWard();
        $guardian = $this->currentParent();

        return view('portals.parent.meetings', [
            ...$this->viewData($ward),
            'requests' => $guardian
                ? MeetingRequest::query()
                    ->where('guardian_id', $guardian->getKey())
                    ->with(['student', 'teacher'])
                    ->latest()
                    ->get()
                : collect(),
            'teachers' => $ward && $guardian?->canAccess('messages', $ward)
                ? $this->wardTeachers($ward)
                : collect(),
        ]);
    }

    private function wardTeachers(Student $ward): Collection
    {
        return User::query()
            ->whereIn('id', ClassSubject::query()->where('class_room_id', $ward->class_room_id)->pluck('teacher_id'))
            ->orderBy('first_name')
            ->get();
    }

    public function meetingRequestsStore(): RedirectResponse
    {
        $guardian = $this->currentParent();
        abort_unless((bool) $guardian, 403);

        $validated = request()->validate([
            'student' => ['required', 'exists:students,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'meeting_type' => ['required', Rule::enum(MeetingRequestType::class)],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'string', 'max:10'],
            'reason' => ['nullable', 'string', 'max:190'],
        ]);

        $ward = $guardian->students()->find($validated['student']);
        abort_unless((bool) $ward, 403);
        abort_unless($guardian->canAccess('messages', $ward), 403);
        abort_unless($this->wardTeachers($ward)->contains('id', $validated['teacher_id']), 403);

        $data = $validated;
        $data['student_id'] = $data['student'];
        unset($data['student']);

        $meeting = MeetingRequest::create([
            ...$data,
            'guardian_id' => $guardian->getKey(),
            'status' => MeetingRequestStatus::Requested->value,
            'requested_at' => now(),
        ]);

        app(NotificationService::class)->sendToUser($validated['teacher_id'], [
            'type' => 'meeting',
            'category' => 'system',
            'priority' => 'medium',
            'icon' => 'calendar',
            'title' => __('Meeting request'),
            'body' => __('A parent requested a :type meeting on :date with you.', [
                'type' => MeetingRequestType::tryFrom($validated['meeting_type'])?->label() ?? $validated['meeting_type'],
                'date' => Carbon::parse($validated['preferred_date'])->format('d M Y'),
            ]),
            'redirect_url' => route('cms.teacher.meetings'),
        ]);

        return to_route('cms.parent.meetings')->with('status', __('Meeting requested. The teacher will confirm.'));
    }

    public function meetingRequestCancel(MeetingRequest $meetingRequest): RedirectResponse
    {
        $guardian = $this->currentParent();
        abort_unless((string) $meetingRequest->guardian_id === (string) $guardian?->getKey(), 403);
        abort_unless($meetingRequest->status === MeetingRequestStatus::Requested, 403);

        $meetingRequest->update(['status' => MeetingRequestStatus::Cancelled->value]);

        return to_route('cms.parent.meetings')->with('status', __('Meeting cancelled.'));
    }

    /* ------------------------------------ Requests --------------------------------- */

    public function requests(): View
    {
        $ward = $this->selectedWard();
        $guardian = $this->currentParent();

        return view('portals.parent.requests', [
            ...$this->viewData($ward),
            'requests' => $guardian
                ? ParentRequest::query()->where('guardian_id', $guardian->getKey())->with('student')->latest()->get()
                : collect(),
        ]);
    }

    public function requestsStore(): RedirectResponse
    {
        $guardian = $this->currentParent();
        abort_unless((bool) $guardian, 403);

        $validated = request()->validate([
            'student' => ['nullable', 'exists:students,id'],
            'type' => ['required', Rule::enum(ParentRequestType::class)],
            'subject' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
        ]);

        $reference = strtoupper(Str::random(8));

        $data = $validated;
        $data['student_id'] = $data['student'];
        unset($data['student']);

        ParentRequest::create([
            ...$data,
            'guardian_id' => $guardian->getKey(),
            'reference_number' => 'REQ-'.now()->format('Y').'-'.$reference,
            'status' => ParentRequestStatus::Submitted->value,
            'submitted_at' => now(),
        ]);

        $this->notifyStaff([
            'type' => 'request',
            'category' => 'system',
            'priority' => 'low',
            'icon' => 'inbox',
            'title' => __('New parent service request'),
            'body' => __(':guardian submitted a new request: :subject.', [
                'guardian' => $guardian->full_name,
                'subject' => $validated['subject'],
            ]),
            'redirect_url' => route('guardian-services.requests.index'),
        ]);

        return to_route('cms.parent.requests')->with('status', __('Request submitted.'));
    }

    /* ----------------------------------- Documents --------------------------------- */

    public function documents(): View
    {
        $ward = $this->selectedWard();
        abort_unless(! $ward || $this->currentParent()?->canAccess('documents', $ward), 403);

        return view('portals.parent.documents', [
            ...$this->viewData($ward),
            'documents' => $ward
                ? StudentDocument::query()->where('student_id', $ward->getKey())->where('verified', true)->latest()->get()
                : collect(),
        ]);
    }

    /* --------------------------------- Notifications ------------------------------- */

    public function notifications(Request $request): View
    {
        $notifications = Notification::query()
            ->where('user_id', auth()->id())
            ->when($request->query('filter') === 'unread', fn (Builder $query) => $query->unread())
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('portals.parent.notifications', [
            ...$this->viewData(),
            'notifications' => $notifications,
            'unreadCount' => Notification::where('user_id', auth()->id())->unread()->count(),
        ]);
    }

    public function notificationsReadAll(): RedirectResponse
    {
        Notification::where('user_id', auth()->id())->unread()->update(['read_at' => now()]);

        return to_route('cms.parent.notifications')->with('status', __('All notifications marked as read.'));
    }

    public function notificationShow(Notification $notification): RedirectResponse
    {
        abort_unless((string) $notification->user_id === (string) auth()->id(), 403);

        $notification->markRead();

        return redirect()->to($notification->redirect_url ?? route('cms.parent.notifications'));
    }

    /* ----------------------------------- Settings ---------------------------------- */

    public function settings(): View
    {
        $guardian = $this->currentParent();

        return view('portals.parent.settings', [
            ...$this->viewData(),
            'guardian' => $guardian,
            'preferences' => NotificationPreference::query()->firstOrCreate(
                ['user_id' => auth()->id()]
            ),
        ]);
    }

    public function settingsUpdate(): RedirectResponse
    {
        $guardian = $this->currentParent();
        abort_unless((bool) $guardian, 403);

        $validated = request()->validate([
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:190'],
            'occupation' => ['nullable', 'string', 'max:120'],
            'notify_email' => ['nullable', 'boolean'],
            'notify_sms' => ['nullable', 'boolean'],
            'notify_push' => ['nullable', 'boolean'],
        ]);

        $guardian->update([
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
        ]);

        NotificationPreference::query()->updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'notify_email' => (bool) ($validated['notify_email'] ?? false),
                'notify_sms' => (bool) ($validated['notify_sms'] ?? false),
                'notify_push' => (bool) ($validated['notify_push'] ?? true),
            ]
        );

        return to_route('cms.parent.settings')->with('status', __('Settings updated.'));
    }
}

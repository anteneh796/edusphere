<?php

namespace App\Domains\Approvals\Controllers;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Services\AcademicsService;
use App\Domains\Approvals\Models\ApprovalRequest;
use App\Domains\Approvals\Services\ApprovalService;
use App\Domains\Students\Models\Student;
use App\Http\Controllers\Controller;
use App\Support\Enums\ApprovalStatus;
use App\Support\Enums\ApprovalType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(
        private readonly ApprovalService $approvals,
        private readonly AcademicsService $academics,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ApprovalRequest::class);

        $query = ApprovalRequest::with(['requester', 'reviewer']);

        if (! auth()->user()->hasPermission('approvals.approve')) {
            $query->where('requested_by_id', auth()->id());
        }

        $requests = $query
            ->ofType($request->query('type'))
            ->ofStatus($request->query('status'))
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('approvals.index', compact('requests'));
    }

    public function create(): View
    {
        $this->authorize('create', ApprovalRequest::class);

        $students = Student::with(['gradeLevel'])->latest('created_at')->get();

        $classes = collect();
        try {
            $classes = ClassRoom::where('academic_year_id', $this->academics->currentYear()->getKey())
                ->orderBy('name')
                ->get(['id', 'name']);
        } catch (\RuntimeException) {
            // No current academic year configured yet; leave class targets empty.
        }

        return view('approvals.create', compact('students', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ApprovalRequest::class);

        $data = $request->validate([
            'type' => ['required', Rule::enum(ApprovalType::class)],
            'reason' => ['nullable', 'string', 'max:1000'],
            'student_id' => ['nullable', 'exists:students,id'],
            'target_grade_id' => ['nullable', 'exists:grade_levels,id'],
            'target_class_id' => ['nullable', 'exists:class_rooms,id'],
            'leave_start' => ['nullable', 'date'],
            'leave_end' => ['nullable', 'date', 'after_or_equal:leave_start'],
            'waiver_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $type = ApprovalType::from($data['type']);

        $needsStudent = in_array($type, [ApprovalType::StudentTransfer, ApprovalType::AdmissionAcceptance], true);

        if ($needsStudent && blank($data['student_id'] ?? null)) {
            throw ValidationException::withMessages([
                'student_id' => 'A student is required for this request type.',
            ]);
        }

        $subjectType = $needsStudent ? Student::class : null;

        $payload = array_filter([
            'target_grade_id' => $data['target_grade_id'] ?? null,
            'target_class_id' => $data['target_class_id'] ?? null,
            'leave_start' => $data['leave_start'] ?? null,
            'leave_end' => $data['leave_end'] ?? null,
            'waiver_amount' => $data['waiver_amount'] ?? null,
        ], fn ($value) => $value !== null);

        $approval = $this->approvals->submit(
            $type,
            auth()->user(),
            $data['reason'] ?? null,
            $subjectType,
            $subjectType ? $data['student_id'] : null,
            $payload,
        );

        return redirect()
            ->route('approvals.show', $approval)
            ->with('status', 'Request submitted for approval.');
    }

    public function show(ApprovalRequest $approval): View
    {
        $this->authorize('view', $approval);

        $approval->load(['requester', 'reviewer']);
        $subject = $approval->subject_type ? $approval->subject : null;

        return view('approvals.show', ['approval' => $approval, 'subject' => $subject]);
    }

    public function review(ApprovalRequest $approval, Request $request): RedirectResponse
    {
        $this->authorize('approve', $approval);

        $validated = $request->validate([
            'action' => ['required', 'in:approve,deny'],
            'reviewer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $status = $validated['action'] === 'approve' ? ApprovalStatus::Approved : ApprovalStatus::Denied;

        $this->approvals->review($approval, $status, auth()->user(), $validated['reviewer_note'] ?? null);

        return redirect()
            ->route('approvals.show', $approval)
            ->with('status', 'Request '.strtolower($status->label()).'.');
    }
}

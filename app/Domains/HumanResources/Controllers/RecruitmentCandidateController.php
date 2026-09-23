<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\HumanResources\Models\Position;
use App\Domains\HumanResources\Models\RecruitmentCandidate;
use App\Domains\HumanResources\Services\EmployeeService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\RecruitmentStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecruitmentCandidateController extends Controller
{
    public function __construct(private readonly EmployeeService $employeeService) {}

    public function index(Request $request): View
    {
        $this->requirePermission('hr.view');

        $candidates = RecruitmentCandidate::query()
            ->with('position:id,name')
            ->when($request->filled('hiring_status') && RecruitmentStatus::tryFrom($request->query('hiring_status')), function ($query) use ($request) {
                $query->where('hiring_status', $request->query('hiring_status'));
            })
            ->when($request->filled('q'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('applied_position', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('hr.candidates.index', compact('candidates'));
    }

    public function create(): View
    {
        $this->requirePermission('hr.create');

        $positions = Position::orderBy('name')->get(['id', 'name']);

        return view('hr.candidates.create', compact('positions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('hr.create');

        $candidate = RecruitmentCandidate::create($this->validated($request, null));

        ActivityLogger::log('registered candidate "'.$candidate->name.'"', 'hr', $candidate->getKey());

        return redirect()
            ->route('hr.candidates.show', $candidate)
            ->with('status', 'Candidate "'.$candidate->name.'" registered.');
    }

    public function show(RecruitmentCandidate $candidate): View
    {
        $this->requirePermission('hr.view');

        $candidate->load('position:id,name');

        return view('hr.candidates.show', compact('candidate'));
    }

    public function update(Request $request, RecruitmentCandidate $candidate): RedirectResponse
    {
        $this->requirePermission('hr.edit');

        $candidate->update($this->validated($request, $candidate));

        ActivityLogger::log('updated candidate "'.$candidate->name.'"', 'hr', $candidate->getKey());

        return redirect()
            ->route('hr.candidates.show', $candidate)
            ->with('status', 'Candidate record updated.');
    }

    /**
     * Record the screening/interview outcome.
     */
    public function decision(Request $request, RecruitmentCandidate $candidate): RedirectResponse
    {
        $this->requirePermission('hr.edit');

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['accepted', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $status = $validated['decision'] === 'accepted' ? RecruitmentStatus::Shortlisted->value : RecruitmentStatus::Rejected->value;

        $candidate->update([
            'decision' => $validated['decision'],
            'decision_notes' => $validated['notes'] ?? null,
            'hiring_status' => $status,
        ]);

        ActivityLogger::log('recorded decision '.$validated['decision'].' for candidate "'.$candidate->name.'"', 'hr', $candidate->getKey());

        return redirect()
            ->route('hr.candidates.show', $candidate)
            ->with('status', 'Decision recorded. Candidate is now '.str_replace('_', ' ', $status).'.');
    }

    /**
     * Hiring a candidate converts them into a permanent employee record.
     */
    public function hire(Request $request, RecruitmentCandidate $candidate): RedirectResponse
    {
        $this->requirePermission('hr.create');

        DB::transaction(function () use ($request, $candidate) {
            $employee = $this->createEmployeeFromCandidate($request, $candidate);

            $candidate->update([
                'hiring_status' => RecruitmentStatus::Hired->value,
                'converted_employee_id' => $employee->getKey(),
                'decision' => $candidate->decision ?? 'accepted',
            ]);

            ActivityLogger::log('hired candidate "'.$candidate->name.'" as employee '.$employee->employee_id, 'hr', $employee->getKey());
        });

        $candidate->refresh();

        return redirect()
            ->route('hr.employees.show', $candidate->converted_employee_id)
            ->with('status', 'Candidate hired and employee record created.');
    }

    public function destroy(RecruitmentCandidate $candidate): RedirectResponse
    {
        $this->requirePermission('hr.delete');

        $candidate->delete();

        ActivityLogger::log('deleted candidate "'.$candidate->name.'"', 'hr', null);

        return redirect()->route('hr.candidates.index')->with('status', 'Candidate removed.');
    }

    private function createEmployeeFromCandidate(Request $request, RecruitmentCandidate $candidate)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'employment_type' => ['required', 'string'],
            'joining_date' => ['nullable', 'date'],
        ]);

        return $this->employeeService->create([
            'full_name' => $validated['full_name'],
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? $candidate->phone,
            'email' => $candidate->email,
            'department_id' => $validated['department_id'] ?? null,
            'position_id' => $validated['position_id'] ?? $candidate->position_id,
            'employment_type' => $validated['employment_type'],
            'joining_date' => $validated['joining_date'] ?? now()->toDateString(),
            'employment_status' => 'probation',
            'create_account' => true,
            'roles' => [$this->staffRoleId()] ?? null,
        ]);
    }

    private function staffRoleId(): ?int
    {
        return DB::table('roles')->where('name', 'teacher')->value('id') ?? null;
    }

    private function validated(Request $request, ?RecruitmentCandidate $candidate): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('hr_recruitment_candidates', 'email')->ignore($candidate?->getKey())],
            'phone' => ['nullable', 'string', 'max:30'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'applied_position' => ['required', 'string', 'max:150'],
            'cv_path' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'interview_date' => ['nullable', 'date'],
            'top_skills' => ['nullable', 'string', 'max:1000'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}
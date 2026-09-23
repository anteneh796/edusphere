<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\Accounts\Models\User;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\PerformanceReview;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\PerformanceReviewStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerformanceReviewController extends Controller
{
    public const CATEGORIES = [
        'teaching_quality' => 'Teaching Quality',
        'classroom_management' => 'Classroom Management',
        'professional_conduct' => 'Professional Conduct',
        'attendance_score' => 'Attendance',
        'student_engagement' => 'Student Engagement',
        'admin_responsibility' => 'Administrative Responsibility',
    ];

    public function index(Request $request): View
    {
        $this->requirePermission('hr.view');

        $reviews = PerformanceReview::query()
            ->with(['employee:id,full_name,employee_id', 'evaluator:id,first_name,last_name'])
            ->when($request->filled('period'), fn ($query, $period) => $query->where('period', $period))
            ->when($request->filled('status'), fn ($query, $status) => $query->where('status', $status))
            ->where('status', '!=', PerformanceReviewStatus::Archived->value)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $periods = PerformanceReview::distinct()->orderByDesc('period')->pluck('period');

        return view('hr.performance.index', compact('reviews', 'periods'));
    }

    public function create(): View
    {
        $this->requirePermission('hr.create');

        $employees = Employee::whereIn('employment_status', ['active', 'probation'])->orderBy('full_name')->get(['id', 'full_name', 'employee_id']);
        $evaluators = User::where('status', 'active')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $period = request('period') ?? now()->format('Y').' H'.(now()->month <= 6 ? 1 : 2);

        return view('hr.performance.create', compact('employees', 'evaluators', 'period'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('hr.create');

        $data = $this->validated($request);

        $review = PerformanceReview::create([
            ...$data,
            'overall_score' => round(array_sum(array_intersect_key($data, array_flip(self::CATEGORIES))) / count(self::CATEGORIES), 2),
            'status' => PerformanceReviewStatus::Completed->value,
        ]);

        ActivityLogger::log('created performance review for '.$review->employee_id, 'hr', $review->getKey());

        return redirect()
            ->route('hr.performance.show', $review)
            ->with('status', 'Performance review saved.');
    }

    public function show(PerformanceReview $review): View
    {
        $this->requirePermission('hr.view');

        $review->load(['employee:id,full_name,employee_id', 'evaluator:id,first_name,last_name', 'employee.department:id,name', 'employee.position:id,name']);

        return view('hr.performance.show', compact('review'));
    }

    public function edit(PerformanceReview $review): View
    {
        $this->requirePermission('hr.edit');

        if ($review->status === PerformanceReviewStatus::Archived->value) {
            return abort(403, 'Archived reviews can no longer be edited.');
        }

        $employees = Employee::whereIn('employment_status', ['active', 'probation'])->orderBy('full_name')->get(['id', 'full_name', 'employee_id']);
        $evaluators = User::where('status', 'active')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('hr.performance.edit', compact('review', 'evaluators', 'employees'));
    }

    public function update(Request $request, PerformanceReview $review): RedirectResponse
    {
        $this->requirePermission('hr.edit');

        $data = $this->validated($request);

        $review->update([
            ...$data,
            'overall_score' => round(array_sum(array_intersect_key($data, array_flip(self::CATEGORIES))) / count(self::CATEGORIES), 2),
        ]);

        ActivityLogger::log('updated performance review for '.$review->employee_id, 'hr', $review->getKey());

        return redirect()
            ->route('hr.performance.show', $review)
            ->with('status', 'Performance review updated.');
    }

    public function status(Request $request, PerformanceReview $review): RedirectResponse
    {
        $this->requirePermission('hr.edit');

        $validated = $request->validate([
            'status' => ['required', Rule::enum(PerformanceReviewStatus::class)],
        ]);

        $review->update(['status' => $validated['status']]);

        ActivityLogger::log($validated['status'].' performance review for '.$review->employee_id, 'hr', $review->getKey());

        return back()->with('status', 'Review marked '.$validated['status'].'.');
    }

    private function validated(Request $request): array
    {
        $categoryRules = [];

        foreach (array_keys(self::CATEGORIES) as $category) {
            $categoryRules[$category] = ['required', 'integer', 'min:1', 'max:100'];
        }

        return $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'period' => ['required', 'string', 'max:20'],
            'evaluator_id' => ['nullable', 'exists:users,id'],
            ...$categoryRules,
            'strengths' => ['nullable', 'string', 'max:4000'],
            'improvements' => ['nullable', 'string', 'max:4000'],
            'recommendations' => ['nullable', 'string', 'max:4000'],
        ]);
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}
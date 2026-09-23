<?php

namespace App\Domains\Admissions\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Requests\DecideAdmissionApplicationRequest;
use App\Domains\Admissions\Requests\StoreAdmissionApplicationRequest;
use App\Domains\Admissions\Requests\UpdateAdmissionApplicationRequest;
use App\Domains\Admissions\Services\AdmissionsDashboardService;
use App\Domains\Admissions\Services\AdmissionService;
use App\Domains\Cms\Models\Inquiry;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\AdmissionStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdmissionApplicationController extends Controller
{
    public function __construct(private readonly AdmissionsDashboardService $dashboardService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AdmissionApplication::class);

        $applications = AdmissionApplication::query()
            ->with(['gradeLevel', 'intakeYear', 'primaryGuardian'])
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->grade($request->query('grade_level_id'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admissions.applications.index', [
            'applications' => $applications,
            'statusOptions' => AdmissionStatus::cases(),
            'gradeLevels' => GradeLevel::ordered()->get(),
            'kpis' => $this->dashboardService->kpis(),
        ]);
    }

    public function applicants(Request $request): View
    {
        $this->authorize('viewAny', AdmissionApplication::class);

        $applications = AdmissionApplication::query()
            ->with(['gradeLevel', 'intakeYear', 'primaryGuardian'])
            ->candidates()
            ->search($request->query('q'))
            ->grade($request->query('grade_level_id'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admissions.applications.applicants', [
            'applications' => $applications,
            'gradeLevels' => GradeLevel::ordered()->get(),
        ]);
    }

    public function waitlistIndex(): View
    {
        $this->authorize('viewAny', AdmissionApplication::class);

        $applications = AdmissionApplication::with(['gradeLevel', 'intakeYear', 'primaryGuardian'])
            ->waitlisted()
            ->paginate(20)
            ->withQueryString();

        return view('admissions.waitlist.index', [
            'applications' => $applications,
            'gradeLevels' => GradeLevel::ordered()->get(),
        ]);
    }

    public function approvalsView(): View
    {
        $this->authorize('viewAny', AdmissionApplication::class);

        $applications = AdmissionApplication::with(['gradeLevel', 'intakeYear', 'primaryGuardian'])
            ->pendingApproval()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admissions.approvals.index', [
            'applications' => $applications,
            'gradeLevels' => GradeLevel::ordered()->get(),
        ]);
    }

    public function show(AdmissionApplication $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'gradeLevel',
            'intakeYear',
            'sourceInquiry',
            'student.gradeLevel',
            'parentUser',
            'decisionBy',
            'createdBy',
            'guardians',
            'documents.verifier',
            'assessments.conductor',
            'communications.creator',
        ]);

        return view('admissions.applications.show', [
            'application' => $application,
            'gradeCapacity' => $application->gradeLevel && $application->intakeYear
                ? (int) app(AdmissionService::class)->capacityFor($application->gradeLevel, $application->intakeYear)
                : null,
            'seatsTaken' => $application->gradeLevel && $application->intakeYear
                ? app(AdmissionService::class)->seatsTaken($application->gradeLevel, $application->intakeYear)
                : null,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AdmissionApplication::class);

        $inquiry = $request->filled('inquiry_id')
            ? Inquiry::find($request->input('inquiry_id'))
            : null;

        return view('admissions.applications.create', [
            'gradeLevels' => GradeLevel::ordered()->get(),
            'years' => AcademicYear::orderByDesc('start_date')->get(),
            'inquiry' => $inquiry,
        ]);
    }

    public function store(StoreAdmissionApplicationRequest $request): RedirectResponse
    {
        $this->authorize('create', AdmissionApplication::class);

        $inquiry = $request->filled('source_inquiry_id')
            ? Inquiry::find($request->input('source_inquiry_id'))
            : null;

        $application = app(AdmissionService::class)
            ->createApplication($request->validated(), $inquiry);

        if ($inquiry) {
            $inquiry->update(['status' => 'in_progress']);
        }

        ActivityLogger::log('created admission application '.$application->application_number, 'admissions', $application->id);

        return redirect()->route('admissions.applications.show', $application)
            ->with('status', 'Admission application '.$application->application_number.' created.');
    }

    public function edit(AdmissionApplication $application): View
    {
        $this->authorize('update', $application);

        $application->load(['guardians', 'primaryGuardian', 'intakeYear']);

        return view('admissions.applications.edit', [
            'application' => $application,
            'gradeLevels' => GradeLevel::ordered()->get(),
            'years' => AcademicYear::orderByDesc('start_date')->get(),
        ]);
    }

    public function update(UpdateAdmissionApplicationRequest $request, AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);

        if (! $request->filled('source_inquiry_id')) {
            $request->merge(['source_inquiry_id' => $application->source_inquiry_id]);
        }

        app(AdmissionService::class)->updateApplication($application, $request->validated());
        ActivityLogger::log('updated admission application '.$application->application_number, 'admissions', $application->id);

        return redirect()->route('admissions.applications.show', $application)
            ->with('status', 'Admission application updated.');
    }

    public function destroy(AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('delete', $application);

        $application->delete();
        ActivityLogger::log('archived admission application '.$application->application_number, 'admissions', $application->id);

        return redirect()->route('admissions.applications.index')
            ->with('status', 'Admission application archived.');
    }

    public function submit(AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('submit', $application);

        return $this->transition('submit', $application, 'Application submitted for review.');
    }

    public function review(AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('review', $application);

        return $this->transition('review', $application, 'Application moved to under review.');
    }

    public function submitForApproval(Request $request, AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('submitForApproval', $application);

        return $this->transition('submitForApproval', $application, 'Application submitted for approval.', $request->string('comment')->toString());
    }

    public function decide(DecideAdmissionApplicationRequest $request, AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('decide', $application);

        try {
            $outcome = app(AdmissionService::class)->decide(
                $application,
                $request->validated('decision'),
                $request->validated('comment'),
                (bool) $request->boolean('force'),
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        ActivityLogger::log("{$request->validated('decision')} admission application {$application->application_number}", 'admissions', $application->id);

        $message = match ($outcome) {
            'approved' => 'Application approved.',
            'rejected' => 'Application rejected.',
            default => 'Application placed on the waiting list.',
        };

        return redirect()->route('admissions.applications.show', $application)->with('status', $message)->setStatusCode(201);
    }

    public function waitlist(Request $request, AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('waitlist', $application);

        return $this->transition('waitlist', $application, 'Application placed on the waiting list.', (string) $request->string('comment'));
    }

    public function promote(AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('promote', $application);

        return $this->transition('promote', $application, 'Candidate promoted from the waiting list.');
    }

    public function withdraw(AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('withdraw', $application);

        return $this->transition('withdraw', $application, 'Application withdrawn.');
    }

    public function enroll(AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('enroll', $application);

        try {
            $student = app(AdmissionService::class)->enroll($application);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        ActivityLogger::log('enrolled '.$student->full_name.' ('.$student->student_number.') via admissions', 'admissions', $student->id);

        return redirect()->route('admissions.applications.show', $application)
            ->with('status', $student->full_name.' enrolled as '.$student->student_number.'.')
            ->setStatusCode(201);
    }

    private function transition(string $method, AdmissionApplication $application, string $success, ?string $parameter = null): RedirectResponse
    {
        try {
            $parameter === null
                ? app(AdmissionService::class)->{$method}($application)
                : app(AdmissionService::class)->{$method}($application, $parameter);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('admissions.applications.show', $application)->with('status', $success)->setStatusCode(201);
    }
}

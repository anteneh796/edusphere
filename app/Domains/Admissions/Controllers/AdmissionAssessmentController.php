<?php

namespace App\Domains\Admissions\Controllers;

use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Models\AdmissionAssessment;
use App\Domains\Admissions\Requests\StoreAdmissionAssessmentRequest;
use App\Domains\Admissions\Requests\UpdateAdmissionAssessmentRequest;
use App\Domains\Admissions\Services\AdmissionService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\AdmissionAssessmentStatus;
use App\Support\Enums\AdmissionAssessmentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdmissionAssessmentController extends Controller
{
    public function __construct(private readonly AdmissionService $service) {}

    public function index(): View
    {
        $this->authorize('viewAny', AdmissionApplication::class);

        $assessments = AdmissionAssessment::with(['application.gradeLevel', 'conductor'])
            ->latest('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        return view('admissions.assessments.index', [
            'assessments' => $assessments,
            'types' => AdmissionAssessmentType::cases(),
            'statusOptions' => AdmissionAssessmentStatus::cases(),
        ]);
    }

    public function store(StoreAdmissionAssessmentRequest $request, AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('scheduleAssessment', $application);

        try {
            $assessment = $this->service->scheduleAssessment($application, $request->validated());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        ActivityLogger::log("scheduled {$assessment->typeLabel()} for {$application->application_number}", 'admissions', $application->id);

        return redirect()->route('admissions.applications.show', $application)
            ->with('status', "{$assessment->typeLabel()} assessment scheduled.");
    }

    public function update(UpdateAdmissionAssessmentRequest $request, AdmissionAssessment $assessment): RedirectResponse
    {
        $this->authorize('recordAssessment', $assessment->application);

        $assessment = $this->service->recordAssessment($assessment, $request->validated());
        ActivityLogger::log("recorded {$assessment->typeLabel()} as {$assessment->statusLabel()}", 'admissions', $assessment->application_id);

        return redirect()->route('admissions.applications.show', $assessment->application)
            ->with('status', 'Assessment updated.');
    }

    public function destroy(AdmissionAssessment $assessment): RedirectResponse
    {
        $this->authorize('update', $assessment->application);

        $assessment->delete();

        return back()->with('status', 'Assessment removed.');
    }
}

<?php

namespace App\Domains\Admissions\Controllers;

use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Services\AdmissionsDashboardService;
use App\Domains\Admissions\Services\AdmissionService;
use Illuminate\Validation\ValidationException;
use App\Domains\Cms\Models\Inquiry;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdmissionsDashboardController extends Controller
{
    public function __construct(
        private readonly AdmissionsDashboardService $service,
        private readonly AdmissionService $admissions,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', AdmissionApplication::class);

        return view('admissions.dashboard', [
            'kpis' => $this->service->kpis(),
            'pipeline' => $this->service->pipeline(),
            'applicationsByGrade' => $this->service->applicationsByGrade(),
            'capacityAlerts' => $this->service->capacityAlerts(),
            'recentApplications' => $this->service->recentApplications(),
            'inquiries' => Inquiry::where('type', 'admissions')->latest()->limit(5)->get(),
        ]);
    }

    public function handleInquiry(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $this->authorize('create', AdmissionApplication::class);

        $data = $request->validate([
            'grade_level_id' => ['required', 'exists:grade_levels,id'],
            'intake_academic_year_id' => ['required', 'exists:academic_years,id'],
        ]);

        try {
            $application = $this->admissions->createDraftFromInquiry(
                $inquiry,
                (string) $data['grade_level_id'],
                (string) $data['intake_academic_year_id'],
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('admissions.applications.show', $application)
            ->with('status', 'Inquiry converted into an admission application draft.');
    }
}

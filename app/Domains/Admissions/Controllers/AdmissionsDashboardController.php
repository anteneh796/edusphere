<?php

namespace App\Domains\Admissions\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Services\AdmissionsDashboardService;
use App\Domains\Cms\Models\Inquiry;
use App\Http\Controllers\Controller;
use App\Support\Enums\AdmissionStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdmissionsDashboardController extends Controller
{
    public function __construct(private readonly AdmissionsDashboardService $service) {}

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
        $this->authorize('viewAny', AdmissionApplication::class);

        $intakeYearId = $request->input('intake_academic_year_id')
            ?? AcademicYear::query()->where('is_current', true)->value('id');

        $application = AdmissionApplication::factory()->create([
            'source_inquiry_id' => $inquiry->getKey(),
            'grade_level_id' => $request->input('grade_level_id'),
            'intake_academic_year_id' => $intakeYearId,
            'status' => AdmissionStatus::Draft->value,
            'applied_at' => null,
            'created_by' => $inquiry->handled_by ?? auth()->id(),
        ]);

        $inquiry->update([
            'status' => $inquiry->status === 'new' ? 'handled' : $inquiry->status,
            'handled_at' => $inquiry->handled_at ?? now(),
            'handled_by' => $inquiry->handled_by ?? auth()->id(),
        ]);

        return redirect()->route('admissions.applications.create', $application)
            ->with('status', 'Inquiry handled into an application draft.');
    }
}

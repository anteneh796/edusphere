<?php

namespace App\Domains\Admissions\Controllers;

use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Services\AdmissionsDashboardService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdmissionReportsController extends Controller
{
    public function __construct(private readonly AdmissionsDashboardService $service) {}

    public function index(): View
    {
        $this->authorize('viewAny', AdmissionApplication::class);

        return view('admissions.reports.index', [
            'kpis' => $this->service->kpis(),
            'funnel' => $this->service->funnel(),
            'monthly' => $this->service->monthlyApplications(),
            'byGrade' => $this->service->applicationsByGrade(),
            'genderSpread' => $this->service->genderSpread(),
            'sourceSpread' => $this->service->sourceSpread(),
            'averageDays' => $this->service->averageDaysToDecision(),
        ]);
    }
}

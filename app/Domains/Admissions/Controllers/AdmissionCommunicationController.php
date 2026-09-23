<?php

namespace App\Domains\Admissions\Controllers;

use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Models\AdmissionCommunication;
use App\Domains\Admissions\Requests\StoreAdmissionCommunicationRequest;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;

class AdmissionCommunicationController extends Controller
{
    public function store(StoreAdmissionCommunicationRequest $request, AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('view', $application);

        $application->communications()->create([
            ...$request->validated(),
            'occurred_at' => $request->date('occurred_at') ?? now(),
            'created_by' => auth()->id(),
        ]);

        ActivityLogger::log("logged {$request->validated('type')} for {$application->application_number}", 'admissions', $application->id);

        return back()->with('status', 'Communication logged.');
    }

    public function destroy(AdmissionApplication $application, AdmissionCommunication $communication): RedirectResponse
    {
        $this->authorize('update', $application);

        $communication->delete();

        return back()->with('status', 'Communication removed.');
    }
}

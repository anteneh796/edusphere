<?php

namespace App\Domains\Admissions\Controllers;

use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Models\ApplicationGuardian;
use App\Domains\Admissions\Requests\StoreApplicationGuardianRequest;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;

class ApplicationGuardianController extends Controller
{
    public function store(StoreApplicationGuardianRequest $request, AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $application->guardians()->create($request->validated());
        ActivityLogger::log('added guardian to application '.$application->application_number, 'admissions', $application->id);

        return back()->with('status', 'Guardian added.');
    }

    public function update(StoreApplicationGuardianRequest $request, AdmissionApplication $application, ApplicationGuardian $guardian): RedirectResponse
    {
        $this->authorize('update', $application);

        if ($guardian->application_id !== $application->getKey()) {
            abort(404);
        }

        $guardian->update($request->validated());
        ActivityLogger::log('updated guardian on application '.$application->application_number, 'admissions', $application->id);

        return back()->with('status', 'Guardian updated.');
    }

    public function destroy(AdmissionApplication $application, ApplicationGuardian $guardian): RedirectResponse
    {
        $this->authorize('update', $application);

        if ($guardian->application_id !== $application->getKey()) {
            abort(404);
        }

        $guardian->delete();

        return back()->with('status', 'Guardian removed.');
    }
}

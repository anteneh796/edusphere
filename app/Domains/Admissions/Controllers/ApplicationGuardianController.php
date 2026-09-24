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

        $application->parents()->create($request->validated());
        ActivityLogger::log('added parent to application '.$application->application_number, 'admissions', $application->id);

        return back()->with('status', 'Parent added.');
    }

    public function update(StoreApplicationGuardianRequest $request, AdmissionApplication $application, ApplicationGuardian $parent): RedirectResponse
    {
        $this->authorize('update', $application);

        if ($parent->application_id !== $application->getKey()) {
            abort(404);
        }

        $parent->update($request->validated());
        ActivityLogger::log('updated parent on application '.$application->application_number, 'admissions', $application->id);

        return back()->with('status', 'Parent updated.');
    }

    public function destroy(AdmissionApplication $application, ApplicationGuardian $parent): RedirectResponse
    {
        $this->authorize('update', $application);

        if ($parent->application_id !== $application->getKey()) {
            abort(404);
        }

        $parent->delete();

        return back()->with('status', 'Parent removed.');
    }
}

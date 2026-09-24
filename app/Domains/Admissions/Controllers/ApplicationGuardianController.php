<?php

namespace App\Domains\Admissions\Controllers;

use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Models\ApplicationParent;
use App\Domains\Admissions\Requests\StoreApplicationParentRequest;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;

class ApplicationParentController extends Controller
{
    public function store(StoreApplicationParentRequest $request, AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $application->parents()->create($request->validated());
        ActivityLogger::log('added parent to application '.$application->application_number, 'admissions', $application->id);

        return back()->with('status', 'Parent added.');
    }

    public function update(StoreApplicationParentRequest $request, AdmissionApplication $application, ApplicationParent $parent): RedirectResponse
    {
        $this->authorize('update', $application);

        if ($parent->application_id !== $application->getKey()) {
            abort(404);
        }

        $parent->update($request->validated());
        ActivityLogger::log('updated parent on application '.$application->application_number, 'admissions', $application->id);

        return back()->with('status', 'Parent updated.');
    }

    public function destroy(AdmissionApplication $application, ApplicationParent $parent): RedirectResponse
    {
        $this->authorize('update', $application);

        if ($parent->application_id !== $application->getKey()) {
            abort(404);
        }

        $parent->delete();

        return back()->with('status', 'Parent removed.');
    }
}

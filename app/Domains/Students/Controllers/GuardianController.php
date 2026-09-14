<?php

namespace App\Domains\Students\Controllers;

use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Requests\StoreGuardianRequest;
use App\Domains\Students\Requests\UpdateGuardianRequest;
use App\Domains\Students\Services\GuardianService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\GuardianRelationship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardianController extends Controller
{
    public function __construct(private readonly GuardianService $guardianService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Guardian::class);

        $guardians = $this->guardianService->paginate($request->only('q'));

        return view('guardians.index', compact('guardians'));
    }

    public function create(): View
    {
        $this->authorize('create', Guardian::class);

        $relationshipOptions = $this->relationshipOptions();

        return view('guardians.create', compact('relationshipOptions'));
    }

    public function store(StoreGuardianRequest $request): RedirectResponse
    {
        $this->authorize('create', Guardian::class);

        $guardian = $this->guardianService->create($request->validated());
        ActivityLogger::log('registered guardian '.$guardian->full_name, 'guardians', $guardian->id);

        return redirect()->route('guardians.show', $guardian)
            ->with('status', 'Guardian "'.$guardian->full_name.'" registered.');
    }

    public function show(Guardian $guardian): View
    {
        $this->authorize('view', $guardian);

        $guardian->load(['students', 'user'])->loadCount('students');

        return view('guardians.show', compact('guardian'));
    }

    public function edit(Guardian $guardian): View
    {
        $this->authorize('update', $guardian);

        $relationshipOptions = $this->relationshipOptions();

        return view('guardians.edit', compact('guardian', 'relationshipOptions'));
    }

    public function update(UpdateGuardianRequest $request, Guardian $guardian): RedirectResponse
    {
        $this->authorize('update', $guardian);

        $this->guardianService->update($guardian, $request->validated());
        ActivityLogger::log('updated guardian '.$guardian->full_name, 'guardians', $guardian->id);

        return redirect()->route('guardians.show', $guardian)
            ->with('status', 'Guardian "'.$guardian->full_name.'" updated.');
    }

    public function destroy(Guardian $guardian): RedirectResponse
    {
        $this->authorize('delete', $guardian);

        $this->guardianService->delete($guardian);
        ActivityLogger::log('deleted guardian '.$guardian->full_name, 'guardians', $guardian->id);

        return redirect()->route('guardians.index')
            ->with('status', 'Guardian "'.$guardian->full_name.'" removed.');
    }

    private function relationshipOptions(): array
    {
        return collect(GuardianRelationship::cases())
            ->mapWithKeys(fn ($relationship) => [$relationship->value => $relationship->label()])
            ->all();
    }
}

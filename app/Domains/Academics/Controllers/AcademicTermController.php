<?php

namespace App\Domains\Academics\Controllers;

use App\Domains\Academics\Models\AcademicTerm;
use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Requests\StoreAcademicTermRequest;
use App\Domains\Academics\Requests\UpdateAcademicTermRequest;
use App\Domains\Academics\Services\AcademicsService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AcademicTermController extends Controller
{
    public function __construct(private readonly AcademicsService $academicsService) {}

    public function index(): View
    {
        $year = $this->academicsService->currentYear();
        $terms = AcademicTerm::where('academic_year_id', $year->getKey())->ordered()->get();
        $years = AcademicYear::orderByDesc('start_date')->get();

        return view('academics.terms.index', compact('terms', 'years', 'year'));
    }

    public function create(): View
    {
        $this->authorize('create', AcademicTerm::class);

        $years = AcademicYear::orderByDesc('start_date')->get();
        $currentYearId = $this->academicsService->currentYear()->getKey();

        return view('academics.terms.create', compact('years', 'currentYearId'));
    }

    public function store(StoreAcademicTermRequest $request): RedirectResponse
    {
        $this->authorize('create', AcademicTerm::class);

        $validated = $request->validated();
        $validated['academic_year_id'] ??= $this->academicsService->currentYear()->getKey();

        $this->assertUniqueName($validated['academic_year_id'], $validated['name']);

        $term = AcademicTerm::create($validated);
        ActivityLogger::log('created term '.$term->name, 'academics', $term->id);

        return redirect()
            ->route('academics.terms.index')
            ->with('status', "Term \"{$term->name}\" created.");
    }

    public function edit(AcademicTerm $term): View
    {
        $this->authorize('update', $term);

        $years = AcademicYear::orderByDesc('start_date')->get();

        return view('academics.terms.edit', compact('term', 'years'));
    }

    public function update(UpdateAcademicTermRequest $request, AcademicTerm $term): RedirectResponse
    {
        $this->authorize('update', $term);

        $this->assertUniqueName($term->academic_year_id, $request->input('name'), $term);

        $term->update($request->validated());
        ActivityLogger::log('updated term '.$term->name, 'academics', $term->id);

        return redirect()
            ->route('academics.terms.index')
            ->with('status', "Term \"{$term->name}\" updated.");
    }

    public function activate(AcademicTerm $term): RedirectResponse
    {
        $this->authorize('update', $term);

        AcademicTerm::where('academic_year_id', $term->academic_year_id)->update(['is_current' => false]);
        $term->update(['is_current' => true]);

        ActivityLogger::log('activated term '.$term->name, 'academics', $term->id);

        return redirect()
            ->route('academics.terms.index')
            ->with('status', "\"{$term->name}\" is now the current term.");
    }

    public function destroy(AcademicTerm $term): RedirectResponse
    {
        $this->authorize('delete', $term);

        if ($term->is_current) {
            return back()->withErrors(['term' => 'The current term cannot be deleted.']);
        }

        $term->delete();
        ActivityLogger::log('deleted term '.$term->name, 'academics', $term->id);

        return redirect()
            ->route('academics.terms.index')
            ->with('status', "Term \"{$term->name}\" deleted.");
    }

    private function assertUniqueName(string $yearId, string $name, ?AcademicTerm $ignore = null): void
    {
        $duplicate = AcademicTerm::where('academic_year_id', $yearId)
            ->where('name', $name)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'name' => "A term \"{$name}\" already exists for this academic year.",
            ]);
        }
    }
}

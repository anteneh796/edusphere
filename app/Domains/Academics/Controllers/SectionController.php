<?php

namespace App\Domains\Academics\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\Section;
use App\Domains\Academics\Requests\StoreSectionRequest;
use App\Domains\Academics\Requests\UpdateSectionRequest;
use App\Domains\Academics\Services\AcademicsService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function __construct(private readonly AcademicsService $academicsService) {}

    public function index(Request $request): View
    {
        $year = AcademicYear::find($request->query('year')) ?? $this->academicsService->currentYear();

        $sections = Section::where('academic_year_id', $year->getKey())->ordered()->get();
        $years = AcademicYear::orderByDesc('start_date')->get();

        return view('academics.sections.index', compact('sections', 'years', 'year'));
    }

    public function create(): View
    {
        $this->authorize('create', Section::class);

        $years = AcademicYear::orderByDesc('start_date')->get();
        $currentYearId = $this->academicsService->currentYear()->getKey();

        return view('academics.sections.create', compact('years', 'currentYearId'));
    }

    public function store(StoreSectionRequest $request): RedirectResponse
    {
        $this->authorize('create', Section::class);

        $validated = $request->validated();
        $validated['academic_year_id'] ??= $this->academicsService->currentYear()->getKey();

        $this->assertUniqueName($validated['academic_year_id'], $validated['name']);

        $section = Section::create($validated);
        ActivityLogger::log('created section '.$section->name, 'academics', $section->id);

        return redirect()
            ->route('academics.sections.index')
            ->with('status', "Section \"{$section->name}\" created.");
    }

    public function edit(Section $section): View
    {
        $this->authorize('update', $section);

        $years = AcademicYear::orderByDesc('start_date')->get();

        return view('academics.sections.edit', compact('section', 'years'));
    }

    public function update(UpdateSectionRequest $request, Section $section): RedirectResponse
    {
        $this->authorize('update', $section);

        $this->assertUniqueName($section->academic_year_id, $request->input('name'), $section);

        $section->update($request->validated());
        ActivityLogger::log('updated section '.$section->name, 'academics', $section->id);

        return redirect()
            ->route('academics.sections.index')
            ->with('status', "Section \"{$section->name}\" updated.");
    }

    public function destroy(Section $section): RedirectResponse
    {
        $this->authorize('delete', $section);

        $section->delete();
        ActivityLogger::log('deleted section '.$section->name, 'academics', $section->id);

        return redirect()
            ->route('academics.sections.index')
            ->with('status', "Section \"{$section->name}\" deleted.");
    }

    private function assertUniqueName(string $yearId, string $name, ?Section $ignore = null): void
    {
        $duplicate = Section::where('academic_year_id', $yearId)
            ->where('name', $name)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'name' => "A section \"{$name}\" already exists for this academic year.",
            ]);
        }
    }
}

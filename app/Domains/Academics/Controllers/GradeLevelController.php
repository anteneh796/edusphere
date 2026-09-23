<?php

namespace App\Domains\Academics\Controllers;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Requests\StoreGradeLevelRequest;
use App\Domains\Academics\Requests\UpdateGradeLevelRequest;
use App\Domains\Academics\Services\AcademicsService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GradeLevelController extends Controller
{
    public function __construct(private readonly AcademicsService $academicsService) {}

    public function index(): View
    {
        $currentYear = $this->academicsService->currentYear();

        $classes = ClassRoom::withCount([
            'students as active_students' => fn ($query) => $query->where('academic_year_id', $currentYear->getKey()),
        ])->where('academic_year_id', $currentYear->getKey())->get();

        $grades = GradeLevel::active()->ordered()->get()
            ->map(fn (GradeLevel $grade) => [
                'grade' => $grade,
                'classes' => $classes->where('grade_level_id', $grade->getKey())->values(),
                'student_count' => (int) $classes->where('grade_level_id', $grade->getKey())->sum('active_students'),
            ]);

        return view('academics.grades.index', compact('grades', 'currentYear'));
    }

    public function create(): View
    {
        $this->authorize('create', GradeLevel::class);

        return view('academics.grades.create');
    }

    public function store(StoreGradeLevelRequest $request): RedirectResponse
    {
        $this->authorize('create', GradeLevel::class);

        $this->assertUniqueCode($request->input('code'));

        $grade = GradeLevel::create($request->validated());
        ActivityLogger::log('created grade '.$grade->name, 'academics', $grade->id);

        return redirect()
            ->route('academics.grades.index')
            ->with('status', "Grade \"{$grade->name}\" created.");
    }

    public function edit(GradeLevel $grade): View
    {
        $this->authorize('update', $grade);

        return view('academics.grades.edit', compact('grade'));
    }

    public function update(UpdateGradeLevelRequest $request, GradeLevel $grade): RedirectResponse
    {
        $this->authorize('update', $grade);

        $this->assertUniqueCode($request->input('code'), $grade);

        $grade->update($request->validated());
        ActivityLogger::log('updated grade '.$grade->name, 'academics', $grade->id);

        return redirect()
            ->route('academics.grades.index')
            ->with('status', "Grade \"{$grade->name}\" updated.");
    }

    public function destroy(GradeLevel $grade): RedirectResponse
    {
        $this->authorize('delete', $grade);

        $grade->delete();

        ActivityLogger::log('deleted grade '.$grade->name, 'academics', $grade->id);

        return redirect()
            ->route('academics.grades.index')
            ->with('status', "Grade \"{$grade->name}\" deleted.");
    }

    private function assertUniqueCode(string $code, ?GradeLevel $ignore = null): void
    {
        $duplicate = GradeLevel::where('code', $code)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'code' => "A grade with the code \"{$code}\" already exists.",
            ]);
        }
    }
}

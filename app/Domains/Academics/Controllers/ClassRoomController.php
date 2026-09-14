<?php

namespace App\Domains\Academics\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Subject;
use App\Domains\Academics\Requests\AssignSubjectsRequest;
use App\Domains\Academics\Requests\StoreClassRoomRequest;
use App\Domains\Academics\Requests\UpdateClassRoomRequest;
use App\Domains\Academics\Services\AcademicsService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClassRoomController extends Controller
{
    public function __construct(private readonly AcademicsService $academicsService) {}

    public function index(): View
    {
        $currentYear = $this->academicsService->currentYear();

        $grades = GradeLevel::ordered()->get()
            ->map(function (GradeLevel $grade) use ($currentYear) {
                $classes = ClassRoom::withCount(['students as active_students', 'assignments as subject_count'])
                    ->where('academic_year_id', $currentYear->getKey())
                    ->where('grade_level_id', $grade->getKey())
                    ->orderBy('name')
                    ->get();

                return [
                    'grade' => $grade,
                    'classes' => $classes,
                    'student_count' => (int) $classes->sum('active_students'),
                ];
            });

        return view('academics.classes.index', compact('grades', 'currentYear'));
    }

    public function create(): View
    {
        $this->authorize('create', ClassRoom::class);

        $gradeLevels = GradeLevel::ordered()->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $currentYearId = $this->academicsService->currentYear()->getKey();

        return view('academics.classes.create', compact('gradeLevels', 'academicYears', 'currentYearId'));
    }

    public function store(StoreClassRoomRequest $request): RedirectResponse
    {
        $this->authorize('create', ClassRoom::class);

        $validated = $request->validated();
        $validated['academic_year_id'] ??= $this->academicsService->currentYear()->getKey();

        $this->assertUniqueClass($validated['grade_level_id'], $validated['academic_year_id'], $validated['name']);

        $classRoom = ClassRoom::create($validated);
        ActivityLogger::log('created class '.$classRoom->name, 'academics', $classRoom->id);

        return redirect()
            ->route('academics.classes.index')
            ->with('status', "Class \"{$classRoom->name}\" created.");
    }

    public function edit(ClassRoom $classRoom): View
    {
        $this->authorize('update', $classRoom);

        $gradeLevels = GradeLevel::ordered()->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $currentYearId = $this->academicsService->currentYear()->getKey();

        return view('academics.classes.edit', compact('classRoom', 'gradeLevels', 'academicYears', 'currentYearId'));
    }

    public function update(UpdateClassRoomRequest $request, ClassRoom $classRoom): RedirectResponse
    {
        $this->authorize('update', $classRoom);

        $validated = $request->validated();
        $validated['academic_year_id'] ??= $this->academicsService->currentYear()->getKey();

        $this->assertUniqueClass($validated['grade_level_id'], $validated['academic_year_id'], $validated['name'], $classRoom);

        $classRoom->update($validated);
        ActivityLogger::log('updated class '.$classRoom->name, 'academics', $classRoom->id);

        return redirect()
            ->route('academics.classes.index')
            ->with('status', "Class \"{$classRoom->name}\" updated.");
    }

    public function destroy(ClassRoom $classRoom): RedirectResponse
    {
        $this->authorize('delete', $classRoom);

        $classRoom->assignments()->delete();
        $classRoom->delete();

        ActivityLogger::log('deleted class '.$classRoom->name, 'academics', $classRoom->id);

        return redirect()
            ->route('academics.classes.index')
            ->with('status', "Class \"{$classRoom->name}\" deleted.");
    }

    public function subjects(ClassRoom $classRoom): View
    {
        $this->authorize('view', $classRoom);

        $classRoom->load(['gradeLevel', 'assignments.subject', 'assignments.teacher']);

        $subjects = Subject::ordered()->get(['id', 'name', 'code']);
        $teachers = $this->academicsService->teachers();

        $existing = $classRoom->assignments
            ->map(fn ($assignment) => [
                'subject_id' => $assignment->subject_id,
                'teacher_id' => $assignment->teacher_id,
                'periods_per_week' => $assignment->periods_per_week,
            ])
            ->values()
            ->all();

        return view('academics.classes.subjects', compact('classRoom', 'subjects', 'teachers', 'existing'));
    }

    public function saveSubjects(AssignSubjectsRequest $request, ClassRoom $classRoom): RedirectResponse
    {
        $this->authorize('update', $classRoom);

        $this->academicsService->assignSubjects($classRoom, $request->validated('subjects', []));

        ActivityLogger::log('updated subjects for class '.$classRoom->name, 'academics', $classRoom->id);

        return redirect()
            ->route('academics.classes.subjects', $classRoom)
            ->with('status', 'Subjects and teachers updated.');
    }

    private function assertUniqueClass(string $gradeId, string $yearId, string $name, ?ClassRoom $ignore = null): void
    {
        $duplicate = ClassRoom::where('grade_level_id', $gradeId)
            ->where('academic_year_id', $yearId)
            ->where('name', $name)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'name' => "A class \"{$name}\" already exists for this grade and academic year.",
            ]);
        }
    }
}

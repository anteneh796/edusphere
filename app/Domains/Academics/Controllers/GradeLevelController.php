<?php

namespace App\Domains\Academics\Controllers;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Services\AcademicsService;
use App\Http\Controllers\Controller;
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

        $grades = GradeLevel::ordered()->get()
            ->map(fn (GradeLevel $grade) => [
                'grade' => $grade,
                'classes' => $classes->where('grade_level_id', $grade->getKey())->values(),
                'student_count' => (int) $classes->where('grade_level_id', $grade->getKey())->sum('active_students'),
            ]);

        return view('academics.grades.index', compact('grades', 'currentYear'));
    }
}

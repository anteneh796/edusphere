<?php

namespace App\Domains\Academics\Controllers;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Subject;
use App\Domains\Academics\Services\AcademicsService;
use App\Domains\Accounts\Models\User;
use App\Http\Controllers\Controller;
use App\Support\Enums\RoleName;
use Illuminate\View\View;

class AcademicsController extends Controller
{
    public function __construct(private readonly AcademicsService $academicsService) {}

    public function index(): View
    {
        $currentYear = $this->academicsService->currentYear();

        $sections = [
            [
                'title' => 'Classes',
                'route' => route('academics.classes.index'),
                'icon' => 'book-open',
                'description' => 'Sections per grade for the current academic year, with capacity and teacher assignments.',
            ],
            [
                'title' => 'Grades',
                'route' => route('academics.grades.index'),
                'icon' => 'graduation',
                'description' => 'KG-1 through Grade 12 level structure and enrollment counts.',
            ],
            [
                'title' => 'Subjects & Teachers',
                'route' => route('academics.subjects.index'),
                'icon' => 'users-large',
                'description' => 'Curriculum subjects and which classes and teachers they are assigned to.',
            ],
        ];

        $stats = [
            'Classes' => ClassRoom::where('academic_year_id', $currentYear->getKey())->count(),
            'Grades' => GradeLevel::count(),
            'Subjects' => Subject::count(),
            'Teachers' => User::whereHas('roles', fn ($query) => $query->where('name', RoleName::Teacher->value))->count(),
        ];

        return view('academics.index', compact('sections', 'stats', 'currentYear'));
    }
}

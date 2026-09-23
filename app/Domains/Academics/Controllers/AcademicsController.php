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
                'description' => 'Kindergarten through Grade 8 level structure (stages KG, Primary 1-4, Upper Primary 5-8).',
            ],
            [
                'title' => 'Subjects & Teachers',
                'route' => route('academics.subjects.index'),
                'icon' => 'users-large',
                'description' => 'Curriculum subjects and which classes and teachers they are assigned to.',
            ],
            [
                'title' => 'Academic Years',
                'route' => route('academics.years.index'),
                'icon' => 'calendar',
                'description' => 'School calendar years. Activate the current year that drives enrollments and classes.',
            ],
            [
                'title' => 'Sections',
                'route' => route('academics.sections.index'),
                'icon' => 'layers',
                'description' => 'Section letters (A, B, C&hellip;) available to classes within an academic year.',
            ],
            [
                'title' => 'Terms',
                'route' => route('academics.terms.index'),
                'icon' => 'list-checks',
                'description' => 'Terms or semesters within the school calendar, including the active term.',
            ],
        ];

        $stats = [
            'Classes' => ClassRoom::where('academic_year_id', $currentYear->getKey())->count(),
            'Grades' => GradeLevel::active()->count(),
            'Subjects' => Subject::count(),
            'Teachers' => User::whereHas('roles', fn ($query) => $query->where('name', RoleName::Teacher->value))->count(),
        ];

        return view('academics.index', compact('sections', 'stats', 'currentYear'));
    }
}

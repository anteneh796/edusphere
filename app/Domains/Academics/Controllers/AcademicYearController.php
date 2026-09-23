<?php

namespace App\Domains\Academics\Controllers;

use App\Domains\Academics\Models\AcademicTerm;
use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Requests\StoreAcademicYearRequest;
use App\Domains\Academics\Requests\UpdateAcademicYearRequest;
use App\Domains\Academics\Services\AcademicsService;
use App\Domains\Students\Models\StudentEnrollment;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function __construct(private readonly AcademicsService $academicsService) {}

    public function index(): View
    {
        $years = AcademicYear::orderByDesc('start_date')->get()
            ->map(fn (AcademicYear $year) => [
                'year' => $year,
                'classes' => ClassRoom::where('academic_year_id', $year->getKey())->count(),
                'enrollments' => StudentEnrollment::where('academic_year_id', $year->getKey())->count(),
                'terms' => AcademicTerm::where('academic_year_id', $year->getKey())->count(),
            ]);

        return view('academics.years.index', compact('years'));
    }

    public function create(): View
    {
        $this->authorize('create', AcademicYear::class);

        return view('academics.years.create');
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $this->authorize('create', AcademicYear::class);

        $year = AcademicYear::create($request->validated());
        ActivityLogger::log('created academic year '.$year->name, 'academics', $year->id);

        return redirect()
            ->route('academics.years.index')
            ->with('status', "Academic year \"{$year->name}\" created.");
    }

    public function edit(AcademicYear $year): View
    {
        $this->authorize('update', $year);

        return view('academics.years.edit', compact('year'));
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $year): RedirectResponse
    {
        $this->authorize('update', $year);

        $year->update($request->validated());
        ActivityLogger::log('updated academic year '.$year->name, 'academics', $year->id);

        return redirect()
            ->route('academics.years.index')
            ->with('status', "Academic year \"{$year->name}\" updated.");
    }

    public function activate(AcademicYear $year): RedirectResponse
    {
        $this->authorize('update', $year);

        AcademicYear::query()->update(['is_current' => false]);
        $year->update(['is_current' => true]);

        ActivityLogger::log('activated academic year '.$year->name, 'academics', $year->id);

        return redirect()
            ->route('academics.years.index')
            ->with('status', "\"{$year->name}\" is now the current academic year.");
    }

    public function destroy(AcademicYear $year): RedirectResponse
    {
        $this->authorize('delete', $year);

        if ($year->is_current) {
            return back()->withErrors(['year' => 'The current academic year cannot be deleted.']);
        }

        $year->delete();
        ActivityLogger::log('deleted academic year '.$year->name, 'academics', $year->id);

        return redirect()
            ->route('academics.years.index')
            ->with('status', "Academic year \"{$year->name}\" deleted.");
    }
}

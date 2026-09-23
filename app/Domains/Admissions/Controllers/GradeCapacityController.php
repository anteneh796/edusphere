<?php

namespace App\Domains\Admissions\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Admissions\Models\GradeCapacity;
use App\Domains\Admissions\Requests\UpdateGradeCapacityRequest;
use App\Domains\Admissions\Services\AdmissionService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GradeCapacityController extends Controller
{
    public function __construct(private readonly AdmissionService $service) {}

    public function index(): View
    {
        $this->authorize('viewAny', GradeCapacity::class);

        $year = AcademicYear::current()->first() ?? AcademicYear::latest('start_date')->first();

        $rows = GradeLevel::ordered()->get()->map(function (GradeLevel $grade) use ($year) {
            $capacity = $this->service->capacityFor($grade, $year);
            $taken = $this->service->seatsTaken($grade, $year);

            return [
                'grade' => $grade,
                'capacity' => $capacity,
                'taken' => $taken,
                'utilization' => $capacity > 0 ? round(($taken / $capacity) * 100) : 0,
                'record' => $year
                    ? GradeCapacity::where('grade_level_id', $grade->getKey())->where('academic_year_id', $year->getKey())->first()
                    : null,
            ];
        });

        return view('admissions.capacity.index', [
            'rows' => $rows,
            'year' => $year,
            'years' => AcademicYear::orderByDesc('start_date')->get(),
        ]);
    }

    public function update(UpdateGradeCapacityRequest $request): RedirectResponse
    {
        $this->authorize('update', GradeCapacity::class);

        $year = AcademicYear::current()->first() ?? AcademicYear::latest('start_date')->first();

        foreach ($request->validated('capacities') as $entry) {
            GradeCapacity::updateOrCreate(
                [
                    'academic_year_id' => $year?->getKey(),
                    'grade_level_id' => $entry['grade_level_id'],
                ],
                [
                    'capacity' => $entry['capacity'],
                    'allow_override' => (bool) ($entry['allow_override'] ?? false),
                    'updated_by' => auth()->id(),
                ]
            );
        }

        ActivityLogger::log('updated grade capacities for '.($year?->name ?? 'current year'), 'admissions');

        return redirect()->route('admissions.capacity.index')
            ->with('status', 'Grade capacities updated.');
    }
}

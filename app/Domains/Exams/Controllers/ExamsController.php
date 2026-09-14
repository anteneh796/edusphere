<?php

namespace App\Domains\Exams\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Exams\Requests\StoreExamRequest;
use App\Domains\Exams\Requests\UpdateExamRequest;
use App\Domains\Exams\Services\ExamsService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\ExamStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ExamsController extends Controller
{
    public function __construct(private readonly ExamsService $examsService) {}

    public function index(): View
    {
        $this->authorize('viewAny', Exam::class);

        $currentYear = $this->examsService->currentYear();

        $exams = Exam::query()
            ->with('academicYear', 'createdBy')
            ->withCount('papers')
            ->when(request('year') !== null, function ($query) {
                $query->where('academic_year_id', request('year'));
            })
            ->when(request('type'), fn ($query, $type) => $query->where('type', $type))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('start_date')
            ->paginate(12)
            ->withQueryString();

        $years = AcademicYear::orderByDesc('start_date')->get();

        $draftCount = Exam::where('status', ExamStatus::Draft->value)->count();
        $publishedCount = Exam::where('status', ExamStatus::Published->value)->count();
        $resultCount = ExamResult::count();

        return view('exams.index', compact('exams', 'years', 'currentYear', 'draftCount', 'publishedCount', 'resultCount'));
    }

    public function create(): View
    {
        $this->authorize('create', Exam::class);

        $years = AcademicYear::orderByDesc('start_date')->get();
        $currentYearId = $this->examsService->currentYear()->getKey();

        return view('exams.create', compact('years', 'currentYearId'));
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $this->authorize('create', Exam::class);

        $exam = Exam::create([
            ...$request->validated(),
            'academic_year_id' => $request->validated('academic_year_id') ?? $this->examsService->currentYear()->getKey(),
            'status' => $request->validated('status') ?? ExamStatus::Draft->value,
            'created_by_id' => $request->user()->id,
        ]);

        ActivityLogger::log("created exam \"{$exam->name}\"", 'exams', $exam->id);

        return redirect()
            ->route('exams.show', $exam)
            ->with('status', "Exam \"{$exam->name}\" created. Assign papers now.");
    }

    public function show(Exam $exam): View
    {
        $this->authorize('view', $exam);

        $exam->load([
            'academicYear',
            'createdBy',
            'papers.subject',
            'papers.classRoom.gradeLevel',
            'papers.results',
        ]);

        $papersByClass = $exam->papers
            ->groupBy(fn ($paper) => $paper->classRoom->getKey())
            ->map(function ($papers) {
                $first = $papers->first();

                return [
                    'classRoom' => $first->classRoom,
                    'papers' => $papers->values(),
                ];
            })
            ->values();

        $summary = [
            'papers' => $exam->papers->count(),
            'entered' => $exam->papers->sum(fn ($paper) => $paper->results->count()),
            'published' => $exam->isPublished(),
        ];

        return view('exams.show', compact('exam', 'papersByClass', 'summary'));
    }

    public function edit(Exam $exam): View
    {
        $this->authorize('update', $exam);

        $years = AcademicYear::orderByDesc('start_date')->get();

        return view('exams.edit', compact('exam', 'years'));
    }

    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $this->authorize('update', $exam);

        $exam->update($request->validated());

        ActivityLogger::log("updated exam \"{$exam->name}\"", 'exams', $exam->id);

        return redirect()
            ->route('exams.show', $exam)
            ->with('status', "Exam \"{$exam->name}\" updated.");
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->authorize('delete', $exam);

        $name = $exam->name;
        $exam->delete();

        ActivityLogger::log("deleted exam \"{$name}\"", 'exams', $exam->id);

        return redirect()
            ->route('exams.index')
            ->with('status', "Exam \"{$name}\" deleted.");
    }

    public function publish(Exam $exam): RedirectResponse
    {
        $this->authorize('update', $exam);

        $exam->update(['status' => ExamStatus::Published->value]);
        ActivityLogger::log("published exam \"{$exam->name}\"", 'exams', $exam->id);

        return redirect()
            ->route('exams.show', $exam)
            ->with('status', "Exam \"{$exam->name}\" published.");
    }

    public function complete(Exam $exam): RedirectResponse
    {
        $this->authorize('update', $exam);

        $exam->update(['status' => ExamStatus::Completed->value]);
        ActivityLogger::log("completed exam \"{$exam->name}\"", 'exams', $exam->id);

        return redirect()
            ->route('exams.show', $exam)
            ->with('status', "Exam \"{$exam->name}\" marked as completed.");
    }
}

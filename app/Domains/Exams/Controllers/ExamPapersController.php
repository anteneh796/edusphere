<?php

namespace App\Domains\Exams\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\Subject;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Requests\SaveExamPapersRequest;
use App\Domains\Exams\Services\ExamsService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ExamPapersController extends Controller
{
    public function __construct(private readonly ExamsService $examsService) {}

    public function edit(Exam $exam): View
    {
        $this->authorize('managePapers', $exam);

        $exam->load('papers');

        $year = $exam->academic_year_id
            ? AcademicYear::find($exam->academic_year_id)
            : $this->examsService->currentYear();

        $classes = ClassRoom::with('gradeLevel')
            ->where('academic_year_id', $year?->getKey())
            ->orderBy('name')
            ->get();

        $subjects = Subject::ordered()->get(['id', 'name', 'code']);

        $existing = $exam->papers
            ->map(fn ($paper) => [
                'class_room_id' => $paper->class_room_id,
                'subject_id' => $paper->subject_id,
                'max_marks' => (string) $paper->max_marks,
                'pass_marks' => (string) $paper->pass_marks,
                'weight' => $paper->weight !== null ? (string) $paper->weight : '',
                'exam_date' => $paper->exam_date?->format('Y-m-d') ?? '',
                'instruction' => $paper->instruction ?? '',
            ])
            ->values()
            ->all();

        return view('exams.papers', compact('exam', 'classes', 'subjects', 'existing'));
    }

    public function update(SaveExamPapersRequest $request, Exam $exam): RedirectResponse
    {
        $this->authorize('managePapers', $exam);

        $this->examsService->savePapers($exam, $request->validated('papers', []));

        ActivityLogger::log("updated papers for exam \"{$exam->name}\"", 'exams', $exam->id);

        return redirect()
            ->route('exams.papers.edit', $exam)
            ->with('status', 'Exam papers updated.');
    }
}

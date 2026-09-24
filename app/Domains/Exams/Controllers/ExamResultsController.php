<?php

namespace App\Domains\Exams\Controllers;

use App\Domains\Exams\Models\ExamSubject;
use App\Domains\Exams\Requests\SaveExamResultsRequest;
use App\Domains\Exams\Services\ExamsService;
use App\Domains\Students\Models\Student;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ExamResultsController extends Controller
{
    public function __construct(private readonly ExamsService $examsService) {}

    public function board(ExamSubject $paper): View
    {
        $this->authorize('enterResults', $paper->exam);

        $paper->load(['exam.academicYear', 'classRoom.gradeLevel', 'subject', 'results.student']);

        $students = Student::query()
            ->where('class_room_id', $paper->class_room_id)
            ->where('academic_year_id', $paper->exam->academic_year_id)
            ->orderBy('student_number')
            ->get();

        $byStudentId = $paper->results->keyBy('student_id');

        $rows = $students->map(function (Student $student) use ($byStudentId) {
            $result = $byStudentId->get($student->getKey());

            return [
                'student' => $student,
                'result' => $result,
            ];
        });

        $summary = $this->examsService->resultSummary($paper);

        return view('exams.results', compact('paper', 'rows', 'summary'));
    }

    public function save(SaveExamResultsRequest $request, ExamSubject $paper): RedirectResponse
    {
        $this->authorize('enterResults', $paper->exam);

        $marks = collect($request->validated('results'))
            ->map(fn ($row) => $row['marks_obtained'] ?? null)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (float) $value);

        if ($marks->some(fn ($mark) => $mark > (float) $paper->max_marks)) {
            throw ValidationException::withMessages([
                'results' => 'Marks cannot exceed the paper maximum of '.$paper->max_marks.'.',
            ]);
        }

        try {
            $saved = $this->examsService->saveResults(
                $paper,
                (string) $request->user()->getKey(),
                $request->validated('results')
            );
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages([
                'results' => $e->getMessage(),
            ]);
        }

        ActivityLogger::log("entered {$saved} result(s) for {$paper->subject->name} in exam \"{$paper->exam->name}\"", 'exams', $paper->exam->id);

        return redirect()
            ->route('exams.results', $paper)
            ->with('status', "{$saved} results saved.");
    }
}

<?php

namespace App\Domains\Exams\Services;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Exams\Models\ExamSubject;
use App\Support\GradeScale;

class ExamsService
{
    public function currentYear(): AcademicYear
    {
        return AcademicYear::current()->orderBy('start_date')->first()
            ?? throw new \RuntimeException('No current academic year is configured.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function savePapers(Exam $exam, array $rows): void
    {
        $keep = [];

        foreach ($rows as $position => $row) {
            if (empty($row['class_room_id']) || empty($row['subject_id'])) {
                continue;
            }

            $paper = ExamSubject::updateOrCreate(
                [
                    'exam_id' => $exam->getKey(),
                    'class_room_id' => $row['class_room_id'],
                    'subject_id' => $row['subject_id'],
                ],
                [
                    'max_marks' => ! empty($row['max_marks']) ? (float) $row['max_marks'] : 100,
                    'pass_marks' => ! empty($row['pass_marks']) ? (float) $row['pass_marks'] : 50,
                    'weight' => ! empty($row['weight']) ? (float) $row['weight'] : null,
                    'exam_date' => ! empty($row['exam_date']) ? $row['exam_date'] : null,
                    'position' => $position,
                    'instruction' => ! empty($row['instruction']) ? $row['instruction'] : null,
                ]
            );

            $keep[] = $paper->getKey();
        }

        $exam->papers()->whereNotIn('id', $keep)->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function saveResults(ExamSubject $paper, string $enteredById, array $rows): int
    {
        $saved = 0;

        foreach ($rows as $row) {
            $studentId = $row['student_id'] ?? null;

            if (! $studentId) {
                continue;
            }

            $marks = $row['marks_obtained'] !== '' && $row['marks_obtained'] !== null
                ? (float) $row['marks_obtained']
                : null;

            $percentage = $marks !== null && (float) $paper->max_marks > 0
                ? ($marks / (float) $paper->max_marks) * 100
                : null;

            ExamResult::updateOrCreate(
                ['exam_subject_id' => $paper->getKey(), 'student_id' => $studentId],
                [
                    'marks_obtained' => $marks,
                    'grade' => $percentage !== null ? GradeScale::letter($percentage) : null,
                    'remarks' => ! empty($row['remarks']) ? $row['remarks'] : null,
                    'entered_by_id' => $enteredById,
                ]
            );

            $saved++;
        }

        return $saved;
    }

    public function resultSummary(ExamSubject $paper): array
    {
        $rows = $paper->results->filter(fn (ExamResult $result) => $result->marks_obtained !== null);

        $percentages = $rows->map(fn (ExamResult $result) => $result->percentage() ?? 0);

        return [
            'total' => $rows->count(),
            'average_percent' => $percentages->isNotEmpty() ? round($percentages->average(), 1) : 0,
            'passing' => $rows->filter(
                fn (ExamResult $result) => $result->percentage() !== null && GradeScale::passes($result->percentage())
            )->count(),
            'failing' => $rows->filter(
                fn (ExamResult $result) => $result->percentage() !== null && ! GradeScale::passes($result->percentage())
            )->count(),
            'best' => $percentages->isNotEmpty() ? $percentages->max() : null,
        ];
    }

    /**
     * Aggregate performance for a student across the papers of one exam.
     */
    public function studentExamSummary(Exam $exam, int $studentId): array
    {
        $papers = $exam->papers()
            ->with(['subject', 'classRoom'])
            ->with(['results' => fn ($query) => $query->where('student_id', $studentId)])
            ->get();

        $rows = collect();

        foreach ($papers as $paper) {
            $result = $paper->results->first();

            if ($result && $result->marks_obtained !== null) {
                $rows->push([
                    'paper' => $paper,
                    'result' => $result,
                ]);
            }
        }

        $avgs = $rows->map(
            fn (array $entry) => (float) $entry['result']->percentage()
        );

        return [
            'papers' => $rows,
            'entered' => $rows->count(),
            'average_percent' => $avgs->isNotEmpty() ? round($avgs->average(), 1) : null,
            'grade' => $avgs->isNotEmpty() ? GradeScale::letter($avgs->average()) : null,
            'passing' => $avgs->filter(fn ($p) => GradeScale::passes($p))->count(),
        ];
    }
}

<?php

namespace App\Domains\Exams\Services;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Exams\Models\ExamSubject;

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
        $paper->loadMissing('exam', 'subject', 'classRoom');

        if (! $paper->exam->isPublished()) {
            throw new \RuntimeException('Results can only be entered after the exam is published.');
        }

        $user = User::findOrFail($enteredById);

        if ($user->hasRole('teacher') && ! ClassSubject::query()
            ->where('class_room_id', $paper->class_room_id)
            ->where('subject_id', $paper->subject_id)
            ->where('teacher_id', $user->getKey())
            ->exists()) {
            throw new \RuntimeException('You are not assigned to this class and subject.');
        }

        $studentIds = collect($rows)
            ->pluck('student_id')
            ->filter()
            ->unique()
            ->values();

        if ($studentIds->isNotEmpty()) {
            $validStudentIds = \App\Domains\Students\Models\Student::query()
                ->whereIn('id', $studentIds)
                ->where('class_room_id', $paper->class_room_id)
                ->where('academic_year_id', $paper->exam->academic_year_id)
                ->pluck('id');

            if ($validStudentIds->count() !== $studentIds->count()) {
                throw new \RuntimeException('Every result student must belong to the paper class and exam academic year.');
            }
        }

        $saved = 0;

        foreach ($rows as $row) {
            $studentId = $row['student_id'] ?? null;

            if (! $studentId) {
                continue;
            }

            $marks = $row['marks_obtained'] !== '' && $row['marks_obtained'] !== null
                ? (float) $row['marks_obtained']
                : null;

            ExamResult::updateOrCreate(
                ['exam_subject_id' => $paper->getKey(), 'student_id' => $studentId],
                [
                    'marks_obtained' => $marks,
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

        $marks = $rows->map(fn (ExamResult $result) => (float) $result->marks_obtained);

        return [
            'total' => $rows->count(),
            'average' => $marks->isNotEmpty() ? round($marks->average(), 2) : null,
            'passing' => $rows->filter(
                fn (ExamResult $result) => (float) $result->marks_obtained >= (float) $paper->pass_marks
            )->count(),
            'failing' => $rows->filter(
                fn (ExamResult $result) => (float) $result->marks_obtained < (float) $paper->pass_marks
            )->count(),
            'best' => $marks->isNotEmpty() ? $marks->max() : null,
        ];
    }

    /**
     * Aggregate performance for a student across the papers of one exam.
     *
     * @return array<string, mixed>
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

        $totalMax = $rows->sum(fn (array $entry) => (float) $entry['paper']->max_marks);
        $totalObtained = $rows->sum(fn (array $entry) => (float) $entry['result']->marks_obtained);

        return [
            'papers' => $rows,
            'entered' => $rows->count(),
            'total_obtained_marks' => round($totalObtained, 2),
            'average_percent' => $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : null,
            'passing' => $rows->filter(
                fn (array $entry) => (float) $entry['result']->marks_obtained >= (float) $entry['paper']->pass_marks
            )->count(),
        ];
    }
}

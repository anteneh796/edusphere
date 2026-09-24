<?php

namespace App\Domains\Exams\Services;

use App\Domains\Academics\Models\AcademicTerm;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ExamSubject;
use App\Domains\Exams\Models\ReportCard;
use App\Domains\Exams\Models\ReportCardItem;
use App\Domains\Students\Models\Student;
use App\Support\Enums\ExamStatus;
use App\Support\Enums\ReportCardStatus;
use Illuminate\Support\Facades\DB;

class ReportCardsService extends ExamsService
{
    /**
     * Generate a report card for one student across every paper of the exam.
     *
     * Snapshot columns (total_max, total_obtained, average_percent, class_rank,
     * class_size) map 1:1 onto the report_cards migration columns.
     */
    public function generateForStudent(Exam $exam, string $studentId, ?string $generatedById = null): ReportCard
    {
        return DB::transaction(function () use ($exam, $studentId, $generatedById) {
            $exam = Exam::query()->lockForUpdate()->findOrFail($exam->getKey());
            $student = Student::findOrFail($studentId);

            if (! in_array($exam->status, [ExamStatus::Published, ExamStatus::Completed], true)) {
                throw new \RuntimeException('Report cards can only be generated for published or completed exams.');
            }

            if (! $student->class_room_id || ! $student->academic_year_id || $student->academic_year_id !== $exam->academic_year_id) {
                throw new \RuntimeException('The student is not enrolled in the exam academic year.');
            }

            $hasPaper = $exam->papers()
                ->where('class_room_id', $student->class_room_id)
                ->exists();

            if (! $hasPaper) {
                throw new \RuntimeException('The student does not belong to a class included in this exam.');
            }

            $card = ReportCard::query()
                ->where('exam_id', $exam->getKey())
                ->where('student_id', $studentId)
                ->lockForUpdate()
                ->first();

            if ($card && ($card->isLocked() || in_array($card->status, [
                ReportCardStatus::Submitted,
                ReportCardStatus::Approved,
                ReportCardStatus::Published,
            ], true))) {
                throw new \RuntimeException('Submitted or published report cards cannot be regenerated.');
            }

            $papers = $exam->papers()
                ->when($student->class_room_id, fn ($query) => $query->where('class_room_id', $student->class_room_id))
                ->with(['subject' => fn ($q) => $q->orderBy('position')])
                ->with(['results' => fn ($q) => $q->where('student_id', $studentId)])
                ->get();

            $totalMax = $papers->sum(fn (ExamSubject $paper) => (float) $paper->max_marks);
            $totalObtained = $papers->sum(fn (ExamSubject $paper) => (float) ($paper->results->first()?->marks_obtained ?? 0));
            $rank = $this->classRank($exam, $student);
            $academicTermId = AcademicTerm::query()
                ->where('academic_year_id', $exam->academic_year_id)
                ->orderByDesc('is_current')
                ->orderBy('sequence')
                ->value('id');

            if (! $academicTermId) {
                throw new \RuntimeException('The exam academic year has no academic term.');
            }

            if (! $card) {
                $card = ReportCard::create([
                    'academic_year_id' => $exam->academic_year_id,
                    'academic_term_id' => $academicTermId,
                    'exam_id' => $exam->getKey(),
                    'student_id' => $studentId,
                    'generated_by_id' => $generatedById,
                    'status' => ReportCardStatus::Generated->value,
                    'total_max_marks' => $totalMax,
                    'total_obtained_marks' => $totalObtained,
                    'average_percent' => $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : null,
                    'class_rank' => $rank['rank'] ?? null,
                    'class_size' => $rank['size'] ?? null,
                ]);
            } else {
                $card->update([
                    'academic_year_id' => $exam->academic_year_id,
                    'academic_term_id' => $academicTermId,
                    'generated_by_id' => $generatedById ?? $card->generated_by_id,
                    'status' => ReportCardStatus::Generated->value,
                    'total_max_marks' => $totalMax,
                    'total_obtained_marks' => $totalObtained,
                    'average_percent' => $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : null,
                    'class_rank' => $rank['rank'] ?? null,
                    'class_size' => $rank['size'] ?? null,
                ]);

                $card->items()->delete();
            }

            foreach ($papers as $paper) {
                $result = $paper->results->first();

                if (! $result || $result->marks_obtained === null) {
                    continue;
                }

                ReportCardItem::create([
                    'report_card_id' => $card->getKey(),
                    'subject_id' => $paper->subject_id,
                    'max_marks' => (float) $paper->max_marks,
                    'pass_marks' => (float) $paper->pass_marks,
                    'marks_obtained' => (float) $result->marks_obtained,
                    'percentage' => $result->percentage(),
                    'passes' => (float) $result->marks_obtained >= (float) $paper->pass_marks,
                ]);
            }

            $this->snapshotFromResults($card, $exam, $student);

            return $card->fresh(['items', 'academicYear', 'academicTerm', 'exam', 'student']);
        });
    }
    /**
     * Standard competition class ranking from total marks for an exam.
     *
     * Ties share the same rank and the next rank is skipped (1, 2, 2, 4…).
     * Only students with at least one entered result are ranked.
     *
     * @return array{rank: ?int, size: int}
     */
    public function classRank(Exam $exam, Student $student): array
    {
        if (! $student->class_room_id) {
            return ['rank' => null, 'size' => 0];
        }

        $totals = $exam->papers()
            ->where('class_room_id', $student->class_room_id)
            ->with(['results' => fn ($q) => $q->whereNotNull('marks_obtained')])
            ->get()
            ->flatMap(fn (ExamSubject $paper) => $paper->results)
            ->groupBy('student_id')
            ->map(fn ($results) => round((float) $results->sum('marks_obtained'), 2))
            ->sortDesc();

        if ($totals->isEmpty()) {
            return ['rank' => null, 'size' => 0];
        }

        $rankMap = [];
        $previousTotal = null;

        foreach ($totals as $candidateId => $total) {
            if ($previousTotal === null || abs($total - $previousTotal) >= 0.001) {
                $currentRank = count($rankMap) + 1;
            }

            $rankMap[$candidateId] = $currentRank;
            $previousTotal = $total;
        }

        return [
            'rank' => $rankMap[$student->getKey()] ?? null,
            'size' => $totals->count(),
        ];
    }

    /**
     * Permanently store the derived academic summary on the card (spec #9/#10/#12).
     */
    protected function snapshotFromResults(ReportCard $card, Exam $exam, Student $student): void
    {
        $rows = $exam->papers()
            ->where('class_room_id', $student->class_room_id)
            ->with(['results' => fn ($q) => $q->where('student_id', $student->getKey())->whereNotNull('marks_obtained')])
            ->get()
            ->flatMap(fn (ExamSubject $paper) => $paper->results);

        $maxMarks = $exam->papers()
            ->where('class_room_id', $student->class_room_id)
            ->get()
            ->sum('max_marks');

        $obtained = $rows->sum('marks_obtained');
        $rank = $this->classRank($exam, $student);

        $card->update([
            'total_obtained_marks' => $obtained,
            'average_percent' => (float) $maxMarks > 0 ? round(((float) $obtained / (float) $maxMarks) * 100, 2) : null,
            'class_rank' => $rank['rank'],
            'class_size' => $rank['size'],
        ]);
    }

    /* ----------------------------- Workflow ----------------------------- */

    public function approve(ReportCard $card, string $userId): void
    {
        DB::transaction(function () use ($card, $userId) {
            $card = ReportCard::query()->lockForUpdate()->findOrFail($card->getKey());

            if ($card->isLocked() || $card->status === ReportCardStatus::Published) {
                throw new \RuntimeException('Published report cards are locked.');
            }

            if (! in_array($card->status, [ReportCardStatus::Generated, ReportCardStatus::Submitted], true)) {
                throw new \RuntimeException('Only generated or submitted report cards can be approved.');
            }

            $card->update([
                'status' => ReportCardStatus::Approved->value,
                'approved_by_id' => $userId,
                'approved_at' => now(),
            ]);
        });
    }

    public function publish(ReportCard $card, string $userId): void
    {
        DB::transaction(function () use ($card, $userId) {
            $card = ReportCard::query()->lockForUpdate()->findOrFail($card->getKey());

            if ($card->isLocked() || $card->status !== ReportCardStatus::Approved) {
                throw new \RuntimeException('Only approved and unlocked report cards can be published.');
            }

            $card->update([
                'status' => ReportCardStatus::Published->value,
                'published_by_id' => $userId,
                'published_at' => now(),
                'locked_at' => now(),
            ]);
        });
    }
}

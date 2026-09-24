<?php

namespace App\Domains\Exams\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ReportCard;
use App\Domains\Exams\Services\ReportCardsService;
use App\Domains\Students\Models\Student;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ReportCardsController extends Controller
{
    public function __construct(private readonly ReportCardsService $cardsService) {}

    public function index(): View
    {
        $cards = ReportCard::query()
            ->with(['student', 'exam', 'academicYear'])
            ->when(request('year'), fn ($query, $year) => $query->where('academic_year_id', $year))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $years = AcademicYear::orderByDesc('start_date')->get();

        return view('report-cards.index', compact('cards', 'years'));
    }

    public function create(): View
    {
        $exams = Exam::with('academicYear')
            ->orderByDesc('start_date')
            ->get();

        $students = Student::query()
            ->with('classRoom.gradeLevel')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('report-cards.create', compact('exams', 'students'));
    }

    public function generate(): RedirectResponse
    {
        $data = request()->validate([
            'exam_id' => ['required', 'exists:exams,id'],
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $exam = Exam::findOrFail($data['exam_id']);
        try {
            $card = $this->cardsService->generateForStudent(
                $exam,
                $data['student_id'],
                (string) auth()->id()
            );
        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()
            ->route('report-cards.show', $card)
            ->with('status', "Report card #{$card->report_card_number} generated.");
    }

    public function show(ReportCard $reportCard): View
    {
        $reportCard->load([
            'student',
            'exam',
            'academicYear',
            'items',
            'comments',
        ]);

        return view('report-cards.show', ['card' => $reportCard]);
    }

    public function approve(ReportCard $reportCard): RedirectResponse
    {
        $this->cardsService->approve($reportCard, auth()->id());

        return redirect()
            ->route('report-cards.show', $reportCard)
            ->with('status', "Report card #{$reportCard->report_card_number} approved.");
    }

    public function publish(ReportCard $reportCard): RedirectResponse
    {
        if (! $reportCard->isPublishable()) {
            return redirect()
                ->route('report-cards.show', $reportCard)
                ->withErrors(['status' => 'Only approved report cards can be published.']);
        }

        try {
            $this->cardsService->publish($reportCard, auth()->id());
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('report-cards.show', $reportCard)
                ->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()
            ->route('report-cards.show', $reportCard)
            ->with('status', "Report card #{$reportCard->report_card_number} published.");
    }
}

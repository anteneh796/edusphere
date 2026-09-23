<?php

namespace App\Domains\Admissions\Services;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Cms\Models\Inquiry;
use App\Support\Enums\AdmissionStatus;
use Illuminate\Support\Collection;

class AdmissionsDashboardService
{
    public function __construct(private readonly AdmissionService $admissions) {}

    public function kpis(): array
    {
        $total = AdmissionApplication::count();
        $candidates = AdmissionApplication::candidates()->count();
        $pendingApproval = AdmissionApplication::pendingApproval()->count();
        $approved = AdmissionApplication::where('status', AdmissionStatus::Approved->value)->count();
        $waitlisted = AdmissionApplication::waitlisted()->count();
        $enrolled = AdmissionApplication::where('status', AdmissionStatus::Enrolled->value)->count();

        return [
            'total' => $total,
            'candidates' => $candidates,
            'pending_approval' => $pendingApproval,
            'approved' => $approved,
            'waitlisted' => $waitlisted,
            'enrolled' => $enrolled,
            'conversion_rate' => $total > 0 ? round(($enrolled / $total) * 100) : 0,
            'unhandled_inquiries' => Inquiry::where('type', 'admissions')->where('status', 'new')->count(),
        ];
    }

    public function pipeline(): Collection
    {
        return collect(AdmissionStatus::cases())
            ->map(fn (AdmissionStatus $status) => [
                'key' => $status->value,
                'label' => $status->label(),
                'count' => AdmissionApplication::where('status', $status->value)->count(),
            ]);
    }

    public function applicationsByGrade(): Collection
    {
        return GradeLevel::ordered()
            ->get()
            ->map(fn (GradeLevel $grade) => [
                'name' => $grade->name,
                'code' => $grade->code,
                'count' => AdmissionApplication::where('grade_level_id', $grade->getKey())->count(),
            ])
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values();
    }

    public function capacityAlerts(): Collection
    {
        $year = AcademicYear::current()->first();

        return GradeLevel::ordered()->get()
            ->map(function (GradeLevel $grade) use ($year) {
                $capacity = $this->admissions->capacityFor($grade, $year);
                $taken = $this->admissions->seatsTaken($grade, $year);

                return [
                    'grade' => $grade,
                    'capacity' => $capacity,
                    'taken' => $taken,
                    'overflow' => $taken >= $capacity,
                    'utilization' => $capacity > 0 ? round(($taken / $capacity) * 100) : 0,
                ];
            })
            ->filter(fn (array $row) => $row['overflow'] || $row['utilization'] >= 75)
            ->values();
    }

    public function recentApplications(int $limit = 6): Collection
    {
        return AdmissionApplication::with(['gradeLevel', 'intakeYear'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /* ---------------------------------- Reports ---------------------------------- */

    public function monthlyApplications(int $months = 6): Collection
    {
        $cutoff = now()->startOfMonth()->subMonths($months - 1);

        return collect(range(0, $months - 1))
            ->map(function (int $offset) use ($cutoff) {
                $month = $cutoff->copy()->addMonths($offset);

                return [
                    'month' => $month->format('M y'),
                    'count' => AdmissionApplication::whereBetween('created_at', [
                        $month->startOfMonth(),
                        $month->copy()->endOfMonth(),
                    ])->count(),
                ];
            });
    }

    public function funnel(): array
    {
        $total = max(AdmissionApplication::count(), 1);

        $stages = [
            'applied' => AdmissionApplication::whereNotIn('status', [AdmissionStatus::Inquiry->value, AdmissionStatus::Draft->value])->count(),
            'submitted' => AdmissionApplication::whereIn('status', [
                AdmissionStatus::Submitted->value,
                AdmissionStatus::UnderReview->value,
                AdmissionStatus::AssessmentScheduled->value,
                AdmissionStatus::PendingApproval->value,
                AdmissionStatus::Approved->value,
                AdmissionStatus::Enrolled->value,
            ])->count(),
            'approved' => AdmissionApplication::whereIn('status', [AdmissionStatus::Approved->value, AdmissionStatus::Enrolled->value])->count(),
            'enrolled' => AdmissionApplication::where('status', AdmissionStatus::Enrolled->value)->count(),
        ];

        return [
            'total' => $total,
            'stages' => collect($stages)->map(fn (int $count) => [
                'count' => $count,
                'rate' => round(($count / $total) * 100),
            ])->all(),
        ];
    }

    public function averageDaysToDecision(): ?float
    {
        $count = AdmissionApplication::whereNotNull('applied_at')
            ->whereNotNull('decided_at')
            ->where('decided_at', '>=', 'applied_at')
            ->count();

        if ($count === 0) {
            return null;
        }

        $totalDays = AdmissionApplication::whereNotNull('applied_at')
            ->whereNotNull('decided_at')
            ->where('decided_at', '>=', 'applied_at')
            ->get()
            ->sum(fn (AdmissionApplication $application) => $application->applied_at->diffInDays($application->decided_at));

        return round($totalDays / $count, 1);
    }

    public function genderSpread(): Collection
    {
        return AdmissionApplication::selectRaw('gender, count(*) as count')
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->get()
            ->map(fn ($row) => [
                'label' => ucfirst($row->gender),
                'count' => (int) $row->count,
            ]);
    }

    public function sourceSpread(): Collection
    {
        $inquiry = AdmissionApplication::whereNotNull('source_inquiry_id')->count();
        $other = max(AdmissionApplication::count() - $inquiry, 0);

        return collect([
            ['label' => 'Website inquiry', 'count' => $inquiry],
            ['label' => 'Walk-in / other', 'count' => $other],
        ]);
    }
}

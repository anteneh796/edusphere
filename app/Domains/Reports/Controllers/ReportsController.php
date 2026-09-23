<?php

namespace App\Domains\Reports\Controllers;

use App\Domains\Academics\Models\AcademicTerm;
use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\User;
use App\Domains\Approvals\Models\ApprovalRequest;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Cms\Models\Inquiry;
use App\Domains\Students\Models\Student;
use App\Domains\Students\Models\StudentEnrollment;
use App\Http\Controllers\Controller;
use App\Support\Enums\AttendanceStatus;
use App\Support\Enums\GradeStage;
use App\Support\Enums\RoleName;
use App\Support\Enums\StaffType;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(): View
    {
        $currentYear = AcademicYear::current()->orderBy('start_date')->first();
        $currentTerm = $currentYear ? AcademicTerm::where('academic_year_id', $currentYear->getKey())->current()->first() : null;

        $kpis = [
            'students' => Student::active()->count(),
            'teachers' => User::whereHas('roles', fn ($query) => $query->where('name', RoleName::Teacher->value))->count(),
            'classes' => $currentYear ? ClassRoom::where('academic_year_id', $currentYear->getKey())->count() : 0,
            'pending_approvals' => ApprovalRequest::pending()->count(),
            'attendance_rate' => $this->attendanceRate(),
            'fee_collection' => null,
        ];

        $enrollment = $this->enrollmentByGrade($currentYear);
        $staffByType = $this->staffByType();
        $inquiries = $this->inquiriesByMonth();
        $approvalPipeline = ApprovalRequest::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return view('reports.index', compact(
            'kpis',
            'enrollment',
            'staffByType',
            'inquiries',
            'approvalPipeline',
            'currentYear',
            'currentTerm',
        ));
    }

    private function attendanceRate(): ?float
    {
        $total = AttendanceRecord::count();

        if ($total === 0) {
            return null;
        }

        $attended = AttendanceRecord::whereIn('status', [
            AttendanceStatus::Present->value,
            AttendanceStatus::Late->value,
            AttendanceStatus::Excused->value,
        ])->count();

        return round(($attended / $total) * 100, 1);
    }

    private function enrollmentByGrade(?AcademicYear $year): Collection
    {
        $yearId = $year?->getKey();

        $counts = StudentEnrollment::query()
            ->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))
            ->selectRaw('grade_level_id, count(*) as total')
            ->groupBy('grade_level_id')
            ->pluck('total', 'grade_level_id');

        return GradeLevel::active()->ordered()->get()->map(function (GradeLevel $grade) use ($counts) {
            $count = (int) $counts->get($grade->getKey(), 0);

            return [
                'name' => $grade->name,
                'code' => $grade->code,
                'stage' => $grade->stage ? GradeStage::from($grade->stage)->label() : null,
                'count' => $count,
            ];
        });
    }

    private function staffByType(): Collection
    {
        $counts = User::whereNotNull('staff_type')
            ->selectRaw('staff_type, count(*) as total')
            ->groupBy('staff_type')
            ->pluck('total', 'staff_type');

        return collect(StaffType::cases())->map(fn (StaffType $type) => [
            'label' => $type->label(),
            'value' => $type->value,
            'count' => (int) $counts->get($type->value, 0),
        ]);
    }

    private function inquiriesByMonth(): Collection
    {
        return Inquiry::query()
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (Inquiry $inquiry) => $inquiry->created_at->format('Y-m'))
            ->map(fn (Collection $group) => [
                'month' => $group->first()->created_at->format('M Y'),
                'count' => $group->count(),
            ])
            ->values()
            ->take(-6);
    }
}

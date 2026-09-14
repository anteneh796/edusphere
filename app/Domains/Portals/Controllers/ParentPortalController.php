<?php

namespace App\Domains\Portals\Controllers;

use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Students\Models\Guardian;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ParentPortalController extends Controller
{
    private function currentGuardian(): ?Guardian
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        return $user?->guardian;
    }

    public function dashboard(): View
    {
        $guardian = $this->currentGuardian();
        $wards = $guardian?->students ?? collect();

        return view('portals.parent.dashboard', [
            'guardian' => $guardian,
            'wards' => $wards,
        ]);
    }

    public function wards(): View
    {
        return $this->dashboard();
    }

    public function wardShow()
    {
        $guardian = $this->currentGuardian();

        $ward = ($guardian?->students)
            ->where('student_number', request()->route('student'))
            ->first();

        abort_unless($ward, 404);

        return view('portals.parent.ward-snapshot', [
            'guardian' => $guardian,
            'ward' => $ward,
            'latestResults' => Schema::hasTable('exam_results')
                ? ExamResult::query()->where('student_id', $ward->getKey())->latest('id')->limit(5)->get()
                : collect(),
            'recentAttendance' => Schema::hasTable('attendance_records')
                ? AttendanceRecord::query()
                    ->with('session')
                    ->where('student_id', $ward->getKey())
                    ->latest('attendance_records.id')
                    ->limit(10)
                    ->get()
                : collect(),
        ]);
    }

    public function billing(): View
    {
        $guardian = $this->currentGuardian();

        return view('portals.parent.billing', [
            'guardian' => $guardian,
            'wards' => $guardian?->students ?? collect(),
        ]);
    }
}

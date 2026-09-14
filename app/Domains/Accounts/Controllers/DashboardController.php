<?php

namespace App\Domains\Accounts\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Accounts\Models\AuditLog;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Students\Models\Student;
use App\Http\Controllers\Controller;
use App\Support\Enums\AttendanceStatus;
use App\Support\Enums\ExamStatus;
use App\Support\Enums\RoleName;
use App\Support\Navigation;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $role = $user?->roles()->value('name');

        if ($role === RoleName::Student->value) {
            return redirect()->route('cms.student.dashboard');
        }

        if ($role === RoleName::Parent->value) {
            return redirect()->route('portals.parent.dashboard');
        }
        $counts = [
            'students' => Schema::hasTable('students') ? Student::count() : 0,
            'teachers' => Schema::hasTable('users') && Schema::hasTable('roles')
                ? User::whereHas('roles', fn ($query) => $query->where('name', RoleName::Teacher->value))->count()
                : 0,
            'classes' => 0,
            'invoices' => Schema::hasTable('invoices') ? 0 : 0,
        ];

        if (Schema::hasTable('class_rooms')) {
            $yearId = AcademicYear::current()->orderBy('start_date')->value('id');
            $counts['classes'] = $yearId ? ClassRoom::where('academic_year_id', $yearId)->count() : ClassRoom::count();
        }

        $accountStats = [
            'users' => User::count(),
            'roles' => Role::count(),
            'audits' => AuditLog::count(),
        ];

        $attendance = [
            'sessions' => 0,
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
        ];

        if (Schema::hasTable('attendance_sessions') && Schema::hasTable('attendance_records')) {
            $sessionIds = AttendanceSession::whereDate('date', today())->pluck('id');
            $attendance['sessions'] = $sessionIds->count();

            $rows = AttendanceRecord::whereIn('attendance_session_id', $sessionIds)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            foreach (AttendanceStatus::values() as $status) {
                $attendance[$status] = (int) ($rows[$status] ?? 0);
            }
        }

        $exams = [
            'draft' => 0,
            'published' => 0,
            'completed' => 0,
            'entries' => 0,
        ];

        if (Schema::hasTable('exams')) {
            $exams['draft'] = Exam::where('status', ExamStatus::Draft->value)->count();
            $exams['published'] = Exam::where('status', ExamStatus::Published->value)->count();
            $exams['completed'] = Exam::where('status', ExamStatus::Completed->value)->count();
        }

        if (Schema::hasTable('exam_results')) {
            $exams['entries'] = ExamResult::count();
        }

        $recentLogs = AuditLog::with('user')->latest()->limit(6)->get();

        $quickLinks = collect(Navigation::forUser(auth()->user()))
            ->pluck('items')
            ->flatten(1)
            ->filter(fn (array $item) => $item['route'] !== 'dashboard')
            ->map(fn (array $item) => [
                'label' => $item['label'],
                'icon' => $item['icon'],
                'url' => route($item['route']),
            ])
            ->values();

        return view('dashboard.index', compact('counts', 'accountStats', 'recentLogs', 'attendance', 'exams', 'quickLinks'));
    }
}

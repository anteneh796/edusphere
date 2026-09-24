<?php

namespace App\Domains\Accounts\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Accounts\Models\AuditLog;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Approvals\Models\ApprovalRequest;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Students\Models\Student;
use App\Http\Controllers\Controller;
use App\Support\Enums\AttendanceStatus;
use App\Support\Enums\ExamStatus;
use App\Support\Enums\RoleName;
use App\Support\Navigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        $role = $user?->roles()->value('name');

        if ($role === RoleName::Student->value) {
            return redirect()->route('cms.student.dashboard');
        }

        if ($role === RoleName::Parent->value) {
            return redirect()->route('cms.parent.dashboard');
        }

        if ($role === RoleName::Teacher->value) {
            return redirect()->route('cms.teacher.dashboard');
        }

        $isSuperAdmin = $role === RoleName::SuperAdmin->value;
        $isSchoolAdmin = in_array($role, [
            RoleName::SuperAdmin->value,
            RoleName::SchoolAdmin->value,
            RoleName::Principal->value,
        ], true);

        $counts = [
            'students' => Schema::hasTable('students') ? Student::count() : 0,
            'teachers' => Schema::hasTable('users') && Schema::hasTable('roles')
                ? User::whereHas('roles', fn ($query) => $query->where('name', RoleName::Teacher->value))->count()
                : 0,
            'classes' => 0,
        ];

        if (Schema::hasTable('class_rooms')) {
            $yearId = AcademicYear::current()->orderBy('start_date')->value('id');
            $counts['classes'] = $yearId ? ClassRoom::where('academic_year_id', $yearId)->count() : ClassRoom::count();
        }

        $accountStats = [
            'users' => $isSchoolAdmin ? User::count() : 0,
            'roles' => $isSchoolAdmin ? Role::count() : 0,
            'audits' => $isSchoolAdmin ? AuditLog::count() : 0,
        ];

        $canSeeAttendance = in_array($role, [
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
            RoleName::Registrar->value,
            RoleName::Teacher->value,
        ], true);

        $attendance = [
            'sessions' => 0,
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
        ];

        if ($canSeeAttendance && Schema::hasTable('attendance_sessions') && Schema::hasTable('attendance_records')) {
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

        $canSeeExams = in_array($role, [
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
            RoleName::Registrar->value,
            RoleName::Teacher->value,
        ], true);

        $exams = [
            'draft' => 0,
            'published' => 0,
            'completed' => 0,
            'entries' => 0,
        ];

        if ($canSeeExams && Schema::hasTable('exams')) {
            $exams['draft'] = Exam::where('status', ExamStatus::Draft->value)->count();
            $exams['published'] = Exam::where('status', ExamStatus::Published->value)->count();
            $exams['completed'] = Exam::where('status', ExamStatus::Completed->value)->count();
        }

        if ($canSeeExams && Schema::hasTable('exam_results')) {
            $exams['entries'] = ExamResult::count();
        }

        $kpis = [
            'students' => Student::active()->count(),
            'staff' => User::query()
                ->whereHas('roles', fn ($query) => $query->whereNotIn('name', [RoleName::Student->value, RoleName::Parent->value]))
                ->count(),
            'attendance_rate' => $this->attendanceRate(),
            'pending_approvals' => ApprovalRequest::pending()->count(),
            'unread_notifications' => Notification::where('user_id', $user->getKey())->unread()->count(),
        ];

        $pendingApprovals = $user->hasPermission('approvals.view')
            ? ApprovalRequest::pending()
                ->when(! $user->hasPermission('approvals.approve'), fn ($query) => $query->where('requested_by_id', $user->getKey()))
                ->with('requester')
                ->latest('submitted_at')
                ->limit(5)
                ->get()
            : collect();

        $canViewApprovals = $user->hasPermission('approvals.view');
        $canViewNotifications = $user->hasPermission('notifications.view');

        $recentNotifications = $canViewNotifications
            ? Notification::where('user_id', $user->getKey())->latest()->limit(5)->get()
            : collect();

        $recentLogs = $isSchoolAdmin ? AuditLog::with('user')->latest()->limit(6)->get() : collect();

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

        return view('dashboard.index', compact(
            'counts',
            'accountStats',
            'recentLogs',
            'attendance',
            'exams',
            'quickLinks',
            'kpis',
            'pendingApprovals',
            'recentNotifications',
            'isSuperAdmin',
            'isSchoolAdmin',
            'canSeeAttendance',
            'canSeeExams',
            'canViewApprovals',
            'canViewNotifications',
        ));
    }

    private function attendanceRate(): ?float
    {
        if (! Schema::hasTable('attendance_records')) {
            return null;
        }

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
}

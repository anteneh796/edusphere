<?php

namespace App\Domains\Portals\Controllers;

use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Students\Models\Student;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    private function currentStudent(): ?Student
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        return $user->student;
    }

    public function dashboard(): View
    {
        $student = $this->currentStudent();

        return view('portals.student.dashboard', [
            'student' => $student,
            'attendance' => $student && Schema::hasTable('attendance_records')
                ? [
                    'present' => AttendanceRecord::query()
                        ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendance_records.attendance_session_id')
                        ->where('attendance_records.student_id', $student->getKey())
                        ->whereDate('attendance_sessions.date', today())
                        ->where('attendance_records.status', 'present')
                        ->count(),
                    'records' => AttendanceRecord::query()
                        ->with('session')
                        ->where('student_id', $student->getKey())
                        ->latest('attendance_records.created_at')
                        ->limit(10)
                        ->get(),
                ]
                : ['present' => 0, 'records' => collect()],
            'results' => $student && Schema::hasTable('exam_results')
                ? ExamResult::query()
                    ->with(['examSubject.subject'])
                    ->where('student_id', $student->getKey())
                    ->latest('created_at')
                    ->limit(10)
                    ->get()
                : collect(),
        ]);
    }

    public function attendance(): View
    {
        $student = $this->currentStudent();

        return view('portals.student.attendance', [
            'student' => $student,
            'records' => $student && Schema::hasTable('attendance_records')
                ? AttendanceRecord::query()
                    ->with('session.classRoom')
                    ->where('student_id', $student->getKey())
                    ->latest('attendance_records.created_at')
                    ->get()
                : collect(),
        ]);
    }

    public function results(): View
    {
        $student = $this->currentStudent();

        return view('portals.student.results', [
            'student' => $student,
            'results' => $student && Schema::hasTable('exam_results')
                ? ExamResult::query()
                    ->with([
                        'examSubject.exam',
                        'examSubject.subject',
                        'examSubject.classRoom',
                    ])
                    ->where('student_id', $student->getKey())
                    ->latest('created_at')
                    ->get()
                : collect(),
        ]);
    }

    public function profile(): View
    {
        return view('portals.student.profile', [
            'student' => $this->currentStudent(),
        ]);
    }
}

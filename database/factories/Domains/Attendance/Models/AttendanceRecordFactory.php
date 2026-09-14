<?php

namespace Database\Factories\Domains\Attendance\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Students\Models\Student;
use App\Support\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition(): array
    {
        return [
            'attendance_session_id' => AttendanceSession::factory(),
            'student_id' => Student::factory(),
            'status' => fake()->randomElement(AttendanceStatus::cases())->value,
            'note' => null,
            'marked_by_id' => User::factory(),
        ];
    }
}

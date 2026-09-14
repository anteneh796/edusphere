<?php

namespace Database\Factories\Domains\Attendance\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Support\Enums\AttendanceSessionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSession>
 */
class AttendanceSessionFactory extends Factory
{
    protected $model = AttendanceSession::class;

    public function definition(): array
    {
        $class = ClassRoom::factory()->create();
        $year = AcademicYear::factory()->current()->create();

        return [
            'class_room_id' => $class->getKey(),
            'academic_year_id' => $year->getKey(),
            'taken_by_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-7 days', 'now')->format('Y-m-d'),
            'status' => AttendanceSessionStatus::Open->value,
            'note' => null,
            'opened_at' => now(),
            'closed_at' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AttendanceSessionStatus::Closed->value,
            'closed_at' => now(),
        ]);
    }
}

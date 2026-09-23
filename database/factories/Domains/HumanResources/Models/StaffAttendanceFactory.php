<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\StaffAttendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffAttendance>
 */
class StaffAttendanceFactory extends Factory
{
    protected $model = StaffAttendance::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'attendance_date' => now()->format('Y-m-d'),
            'status' => fake()->randomElement(['present', 'present', 'late', 'absent']),
            'check_in' => '08:15',
            'check_out' => '16:30',
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}
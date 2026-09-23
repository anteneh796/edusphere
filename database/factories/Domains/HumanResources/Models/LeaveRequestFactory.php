<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\LeaveRequest;
use App\Domains\HumanResources\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d');
        $days = fake()->numberBetween(1, 10);

        return [
            'employee_id' => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'start_date' => $start,
            'end_date' => now()->parse($start)->addDays($days)->format('Y-m-d'),
            'days' => $days,
            'reason' => fake()->optional()->sentence(),
            'status' => 'pending',
            'submitted_at' => now(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'approved']);
    }
}
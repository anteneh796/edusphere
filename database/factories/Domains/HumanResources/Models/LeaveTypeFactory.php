<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\HumanResources\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Annual Leave', 'Sick Leave', 'Maternity Leave', 'Study Leave']),
            'code' => strtoupper(fake()->unique()->lexify('LV???')),
            'days_per_year' => fake()->numberBetween(5, 45),
            'is_paid' => true,
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\HumanResources\Models\Department;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\EmploymentContract;
use App\Domains\HumanResources\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmploymentContract>
 */
class EmploymentContractFactory extends Factory
{
    protected $model = EmploymentContract::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'contract_number' => 'CTR-'.fake()->unique()->numerify('####'),
            'employment_type' => 'full_time',
            'position_id' => Position::factory(),
            'department_id' => Department::factory(),
            'start_date' => $start = fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
             'end_date' => ($endDate = fake()->optional(0.7)->dateTimeBetween($start, '+2 years')) !== null
                ? $endDate->format('Y-m-d')
                : null,
            'salary_grade' => fake()->optional()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1']),
            'basic_salary' => fake()->optional()->numberBetween(15000, 90000),
            'working_hours_per_week' => fake()->optional()->randomElement([20, 25, 30, 40]),
            'renewal_status' => 'active',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function expiring(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_date' => now()->addDays(fake()->numberBetween(5, 80)),
            'renewal_status' => 'active',
        ]);
    }
}
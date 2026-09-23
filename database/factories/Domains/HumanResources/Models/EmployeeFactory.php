<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\HumanResources\Models\Department;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'employee_id' => 'EMP-'.fake()->unique()->numerify('####'),
            'full_name' => fake()->name(),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-55 years', '-22 years')->format('Y-m-d'),
            'phone' => fake()->numerify('+2519########'),
            'email' => fake()->unique()->safeEmail(),
            'department_id' => Department::factory(),
            'position_id' => Position::factory(),
            'employment_type' => 'full_time',
            'joining_date' => fake()->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'employment_status' => 'active',
            'qualification' => fake()->randomElement(['B.Ed', 'M.Sc', 'BSc', 'BA', 'M.Ed']),
            'university' => fake()->company(),
            'years_of_experience' => fake()->numberBetween(1, 25),
        ];
    }

    public function resigned(): static
    {
        return $this->state(fn (array $attributes) => ['employment_status' => 'resigned']);
    }

    public function withEmployeeId(string $id): static
    {
        return $this->state(fn (array $attributes) => ['employee_id' => $id]);
    }
}
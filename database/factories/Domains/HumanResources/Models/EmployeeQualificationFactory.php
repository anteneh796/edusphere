<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\EmployeeQualification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeQualification>
 */
class EmployeeQualificationFactory extends Factory
{
    protected $model = EmployeeQualification::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'type' => 'qualification',
            'title' => fake()->randomElement(['BSc in Computer Science', 'Bachelor of Education', 'MSc in Mathematics']),
            'institution' => fake()->company(),
            'awarded_on' => fake()->dateTimeBetween('-20 years', '-1 year')->format('Y-m-d'),
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}
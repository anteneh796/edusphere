<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\TrainingRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingRecord>
 */
class TrainingRecordFactory extends Factory
{
    protected $model = TrainingRecord::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'course_name' => fake()->randomElement(['Classroom Management', 'Educational Technology', 'First Aid Certification', 'Leadership Training']),
            'provider' => fake()->company(),
            'trained_on' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'completed_on' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'hours' => fake()->numberBetween(4, 80),
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}
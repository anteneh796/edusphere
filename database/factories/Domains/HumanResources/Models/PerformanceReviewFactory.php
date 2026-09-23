<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\PerformanceReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformanceReview>
 */
class PerformanceReviewFactory extends Factory
{
    protected $model = PerformanceReview::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'period' => '2026 H1',
            'evaluator_id' => User::factory(),
            'teaching_quality' => fake()->numberBetween(60, 100),
            'classroom_management' => fake()->numberBetween(60, 100),
            'professional_conduct' => fake()->numberBetween(60, 100),
            'attendance_score' => fake()->numberBetween(60, 100),
            'student_engagement' => fake()->numberBetween(60, 100),
            'admin_responsibility' => fake()->numberBetween(60, 100),
            'strengths' => fake()->optional()->paragraph(),
            'improvements' => fake()->optional()->paragraph(),
            'recommendations' => fake()->optional()->paragraph(),
            'status' => 'completed',
        ];
    }
}
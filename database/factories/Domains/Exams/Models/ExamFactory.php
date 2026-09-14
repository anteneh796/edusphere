<?php

namespace Database\Factories\Domains\Exams\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Accounts\Models\User;
use App\Domains\Exams\Models\Exam;
use App\Support\Enums\ExamStatus;
use App\Support\Enums\ExamType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory()->current(),
            'created_by_id' => User::factory(),
            'name' => fake()->randomElement(['Midterm Examination', 'Final Examination']),
            'type' => fake()->randomElement([ExamType::Midterm->value, ExamType::Final->value]),
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'status' => ExamStatus::Draft->value,
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExamStatus::Published->value,
        ]);
    }
}

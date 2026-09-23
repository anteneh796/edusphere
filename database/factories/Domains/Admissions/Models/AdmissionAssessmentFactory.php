<?php

namespace Database\Factories\Domains\Admissions\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Models\AdmissionAssessment;
use App\Support\Enums\AdmissionAssessmentStatus;
use App\Support\Enums\AdmissionAssessmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdmissionAssessment>
 */
class AdmissionAssessmentFactory extends Factory
{
    protected $model = AdmissionAssessment::class;

    public function definition(): array
    {
        return [
            'application_id' => AdmissionApplication::factory(),
            'type' => fake()->randomElement(AdmissionAssessmentType::cases())->value,
            'scheduled_at' => fake()->dateTimeBetween('+1 day', '+2 weeks'),
            'location' => fake()->optional()->streetName(),
            'status' => AdmissionAssessmentStatus::Scheduled->value,
            'created_by' => User::factory(),
        ];
    }

    public function completed(int $score = 80): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdmissionAssessmentStatus::Completed->value,
            'score' => $score,
            'notes' => fake()->optional()->sentence(),
            'conducted_at' => now(),
            'conducted_by' => User::factory(),
        ]);
    }
}

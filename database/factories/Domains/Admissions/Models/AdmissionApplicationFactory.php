<?php

namespace Database\Factories\Domains\Admissions\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\User;
use App\Domains\Admissions\Models\AdmissionApplication;
use App\Support\Enums\AdmissionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdmissionApplication>
 */
class AdmissionApplicationFactory extends Factory
{
    protected $model = AdmissionApplication::class;

    public function definition(): array
    {
        return [
            'application_number' => 'ADM-'.fake()->unique()->numerify('####'),
            'status' => AdmissionStatus::Draft->value,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'other_names' => fake()->boolean(30) ? fake()->firstName() : null,
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-14 years', '-5 years'),
            'national_id' => fake()->optional()->numerify('##########'),
            'address' => fake()->optional()->address(),
            'previous_school' => fake()->optional()->company(),
            'intake_academic_year_id' => AcademicYear::factory(),
            'grade_level_id' => GradeLevel::factory(),
            'created_by' => User::factory(),
        ];
    }

    public function inquiry(): static
    {
        return $this->state(fn (array $attributes) => ['status' => AdmissionStatus::Inquiry->value]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => AdmissionStatus::Draft->value]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdmissionStatus::Submitted->value,
            'applied_at' => now(),
        ]);
    }

    public function underReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdmissionStatus::UnderReview->value,
            'applied_at' => now()->subDays(2),
        ]);
    }

    public function assessmentScheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdmissionStatus::AssessmentScheduled->value,
            'applied_at' => now()->subDays(2),
        ]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdmissionStatus::PendingApproval->value,
            'applied_at' => now()->subDays(3),
        ]);
    }

    public function approved(?string $decisionComment = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdmissionStatus::Approved->value,
            'decision' => 'approved',
            'decision_comment' => $decisionComment,
            'decided_at' => now(),
            'decision_by' => User::factory(),
            'applied_at' => now()->subDays(3),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdmissionStatus::Rejected->value,
            'decision' => 'rejected',
            'decided_at' => now(),
            'decision_by' => User::factory(),
        ]);
    }

    public function waitlisted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdmissionStatus::Waitlisted->value,
            'decision' => 'waitlisted',
            'decided_at' => now(),
            'waitlisted_at' => now(),
            'decision_by' => User::factory(),
            'waitlist_position' => fake()->numberBetween(1, 20),
        ]);
    }

    public function withdrawn(): static
    {
        return $this->state(fn (array $attributes) => ['status' => AdmissionStatus::Withdrawn->value]);
    }

    public function enrolled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdmissionStatus::Enrolled->value,
            'decision' => 'approved',
            'decided_at' => now()->subDays(2),
            'enrolled_at' => now(),
            'decision_by' => User::factory(),
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (AdmissionApplication $application) {
            if ($application->guardians()->doesntExist()) {
                $application->guardians()->create([
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'relationship' => 'guardian',
                    'phone' => fake()->optional()->phoneNumber(),
                    'email' => fake()->optional()->safeEmail(),
                    'is_primary' => true,
                ]);
            }
        });
    }
}

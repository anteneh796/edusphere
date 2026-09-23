<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\HumanResources\Models\Position;
use App\Domains\HumanResources\Models\RecruitmentCandidate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecruitmentCandidate>
 */
class RecruitmentCandidateFactory extends Factory
{
    protected $model = RecruitmentCandidate::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('+2519########'),
            'position_id' => Position::factory(),
            'applied_position' => fake()->randomElement(['Mathematics Teacher', 'Accountant', 'Librarian', 'IT Administrator']),
            'interview_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'decision' => null,
            'hiring_status' => 'applied',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function hired(): static
    {
        return $this->state(fn (array $attributes) => ['hiring_status' => 'hired', 'decision' => 'accepted']);
    }
}
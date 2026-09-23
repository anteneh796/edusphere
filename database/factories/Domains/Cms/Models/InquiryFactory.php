<?php

namespace Database\Factories\Domains\Cms\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Cms\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    protected $model = Inquiry::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['admissions', 'general', 'visit']),
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->numerify('+251 9#########'),
            'student_name' => fake()->optional(0.6)->name(),
            'grade_level' => fake()->optional(0.6)->randomElement(['KG 2', 'Grade 1', 'Grade 4', 'Grade 7', 'Grade 10']),
            'message' => fake()->optional()->paragraph(),
            'status' => 'new',
            'handled_at' => null,
            'handled_by' => null,
        ];
    }

    public function handled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'handled',
            'handled_at' => now(),
            'handled_by' => User::factory(),
        ]);
    }
}

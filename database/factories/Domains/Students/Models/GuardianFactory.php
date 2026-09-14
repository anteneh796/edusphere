<?php

namespace Database\Factories\Domains\Students\Models;

use App\Domains\Students\Models\Guardian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guardian>
 */
class GuardianFactory extends Factory
{
    protected $model = Guardian::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'relationship' => fake()->randomElement(['father', 'mother', 'guardian']),
            'phone' => fake()->numerify('+2519########'),
            'email' => fake()->optional()->safeEmail(),
            'occupation' => fake()->optional()->jobTitle(),
            'address' => fake()->optional()->address(),
        ];
    }
}

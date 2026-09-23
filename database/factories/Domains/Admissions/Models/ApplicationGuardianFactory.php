<?php

namespace Database\Factories\Domains\Admissions\Models;

use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Models\ApplicationGuardian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationGuardian>
 */
class ApplicationGuardianFactory extends Factory
{
    protected $model = ApplicationGuardian::class;

    public function definition(): array
    {
        return [
            'application_id' => AdmissionApplication::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'relationship' => fake()->randomElement(['father', 'mother', 'guardian', 'sibling']),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'occupation' => fake()->optional()->jobTitle(),
            'national_id' => fake()->optional()->numerify('##########'),
            'address' => fake()->optional()->address(),
            'is_primary' => false,
            'is_emergency' => fake()->boolean(20),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => ['is_primary' => true]);
    }
}

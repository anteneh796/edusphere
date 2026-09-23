<?php

namespace Database\Factories\Domains\Students\Models;

use App\Domains\Students\Models\EmergencyContact;
use App\Domains\Students\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmergencyContact>
 */
class EmergencyContactFactory extends Factory
{
    protected $model = EmergencyContact::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'name' => fake()->name(),
            'relationship' => fake()->randomElement(['mother', 'father', 'aunt', 'uncle', 'grandparent', 'neighbour']),
            'phone' => '+2519'.fake()->numerify('#######'),
            'priority' => fake()->numberBetween(1, 3),
            'authorized_pickup' => fake()->boolean(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

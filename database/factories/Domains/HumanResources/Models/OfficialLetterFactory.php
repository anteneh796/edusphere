<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\OfficialLetter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfficialLetter>
 */
class OfficialLetterFactory extends Factory
{
    protected $model = OfficialLetter::class;

    public function definition(): array
    {
        return [
            'reference_number' => 'LET-'.fake()->unique()->numerify('####'),
            'employee_id' => Employee::factory(),
            'letter_type' => 'employment',
            'title' => fake()->randomElement(['Appointment Letter', 'Employment Letter']),
            'content' => fake()->paragraphs(3, true),
            'issued_by_id' => User::factory(),
            'issued_on' => now()->format('Y-m-d'),
        ];
    }
}
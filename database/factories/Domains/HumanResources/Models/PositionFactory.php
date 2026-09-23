<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\HumanResources\Models\Department;
use App\Domains\HumanResources\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name' => fake()->randomElement(['Homeroom Teacher', 'Subject Teacher', 'Accountant', 'HR Officer', 'Librarian']),
            'category' => 'academic',
            'responsibilities' => fake()->optional()->paragraph(),
            'is_active' => true,
        ];
    }
}
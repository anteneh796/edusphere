<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\HumanResources\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Executive Office', 'Academic Department', 'Finance', 'Human Resources', 'IT Department']),
            'code' => strtoupper(fake()->unique()->lexify('DEPT???')),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
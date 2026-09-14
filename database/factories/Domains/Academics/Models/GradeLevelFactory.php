<?php

namespace Database\Factories\Domains\Academics\Models;

use App\Domains\Academics\Models\GradeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeLevel>
 */
class GradeLevelFactory extends Factory
{
    protected $model = GradeLevel::class;

    public function definition(): array
    {
        $grades = [
            'KG1' => ['name' => 'Kindergarten 1', 'code' => 'KG1'],
            'KG2' => ['name' => 'Kindergarten 2', 'code' => 'KG2'],
            '1' => ['name' => 'Grade 1', 'code' => '1'],
            '2' => ['name' => 'Grade 2', 'code' => '2'],
            '3' => ['name' => 'Grade 3', 'code' => '3'],
            '4' => ['name' => 'Grade 4', 'code' => '4'],
            '5' => ['name' => 'Grade 5', 'code' => '5'],
            '6' => ['name' => 'Grade 6', 'code' => '6'],
            '7' => ['name' => 'Grade 7', 'code' => '7'],
            '8' => ['name' => 'Grade 8', 'code' => '8'],
            '9' => ['name' => 'Grade 9', 'code' => '9'],
            '10' => ['name' => 'Grade 10', 'code' => '10'],
            '11' => ['name' => 'Grade 11', 'code' => '11'],
            '12' => ['name' => 'Grade 12', 'code' => '12'],
        ];

        $grade = fake()->randomElement($grades);

        return [
            'name' => $grade['name'],
            'code' => $grade['code'],
            'sort_order' => (int) $grade['code'] + 2,
        ];
    }
}

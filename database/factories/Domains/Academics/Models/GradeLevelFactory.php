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
        static $order = 0;

        $grades = [
            'KG1' => ['name' => 'Kindergarten 1', 'stage' => 'kindergarten'],
            'KG2' => ['name' => 'Kindergarten 2', 'stage' => 'kindergarten'],
            '1' => ['name' => 'Grade 1', 'stage' => 'lower_primary'],
            '2' => ['name' => 'Grade 2', 'stage' => 'lower_primary'],
            '3' => ['name' => 'Grade 3', 'stage' => 'lower_primary'],
            '4' => ['name' => 'Grade 4', 'stage' => 'lower_primary'],
            '5' => ['name' => 'Grade 5', 'stage' => 'upper_primary'],
            '6' => ['name' => 'Grade 6', 'stage' => 'upper_primary'],
            '7' => ['name' => 'Grade 7', 'stage' => 'upper_primary'],
            '8' => ['name' => 'Grade 8', 'stage' => 'upper_primary'],
        ];

        $code = array_rand($grades);
        $order = ++$order;

        return [
            'name' => $grades[$code]['name'].' '.$order,
            'code' => $code.'-F'.$order,
            'stage' => $grades[$code]['stage'],
            'is_active' => true,
            'sort_order' => $order,
        ];
    }
}

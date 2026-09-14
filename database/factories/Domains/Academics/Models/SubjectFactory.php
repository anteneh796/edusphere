<?php

namespace Database\Factories\Domains\Academics\Models;

use App\Domains\Academics\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'code' => strtoupper(substr(md5($name), 0, 6)),
            'description' => null,
            'sort_order' => 0,
        ];
    }
}

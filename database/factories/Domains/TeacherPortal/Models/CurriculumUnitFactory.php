<?php

namespace Database\Factories\Domains\TeacherPortal\Models;

use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Domains\TeacherPortal\Models\CurriculumUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CurriculumUnit>
 */
class CurriculumUnitFactory extends Factory
{
    protected $model = CurriculumUnit::class;

    public function definition(): array
    {
        return [
            'class_subject_id' => ClassSubject::factory(),
            'teacher_id' => User::factory(),
            'position' => 1,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'total_lessons' => fake()->numberBetween(4, 12),
            'covered_lessons' => fake()->numberBetween(0, 4),
            'status' => 'pending',
            'started_at' => null,
            'completed_at' => null,
        ];
    }
}

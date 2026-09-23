<?php

namespace Database\Factories\Domains\TeacherPortal\Models;

use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Domains\TeacherPortal\Models\LessonPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonPlan>
 */
class LessonPlanFactory extends Factory
{
    protected $model = LessonPlan::class;

    public function definition(): array
    {
        return [
            'class_subject_id' => ClassSubject::factory(),
            'teacher_id' => User::factory(),
            'week_number' => 1,
            'unit' => fake()->words(3, true),
            'topic' => fake()->sentence(4),
            'objectives' => fake()->paragraph(),
            'materials' => fake()->sentence(),
            'activities' => fake()->paragraph(),
            'assessment' => fake()->sentence(),
            'homework' => fake()->sentence(),
            'status' => 'draft',
            'scheduled_date' => fake()->date(),
        ];
    }
}

<?php

namespace Database\Factories\Domains\TeacherPortal\Models;

use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Domains\TeacherPortal\Models\HomeworkAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomeworkAssignment>
 */
class HomeworkAssignmentFactory extends Factory
{
    protected $model = HomeworkAssignment::class;

    public function definition(): array
    {
        return [
            'class_subject_id' => ClassSubject::factory(),
            'teacher_id' => User::factory(),
            'title' => fake()->sentence(4),
            'instructions' => fake()->paragraph(),
            'assigned_on' => now()->toDateString(),
            'due_on' => now()->addDays(3)->toDateString(),
            'max_marks' => 20,
            'visibility' => 'class',
            'status' => 'draft',
        ];
    }
}

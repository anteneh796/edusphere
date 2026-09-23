<?php

namespace Database\Factories\Domains\TeacherPortal\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Student;
use App\Domains\TeacherPortal\Models\BehaviorNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BehaviorNote>
 */
class BehaviorNoteFactory extends Factory
{
    protected $model = BehaviorNote::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'teacher_id' => User::factory(),
            'class_subject_id' => null,
            'type' => 'observation',
            'severity' => 'info',
            'recorded_on' => now()->toDateString(),
            'note' => fake()->paragraph(),
            'action_taken' => fake()->optional()->sentence(),
            'visibility' => 'teacher',
        ];
    }
}

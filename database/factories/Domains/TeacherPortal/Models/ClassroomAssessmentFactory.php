<?php

namespace Database\Factories\Domains\TeacherPortal\Models;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Domains\TeacherPortal\Models\ClassroomAssessment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassroomAssessment>
 */
class ClassroomAssessmentFactory extends Factory
{
    protected $model = ClassroomAssessment::class;

    public function definition(): array
    {
        return [
            'class_subject_id' => ClassSubject::factory(),
            'class_room_id' => ClassRoom::factory(),
            'teacher_id' => User::factory(),
            'title' => fake()->sentence(4),
            'type' => 'quiz',
            'status' => 'draft',
            'total_marks' => 100,
            'assessment_date' => fake()->date(),
            'room' => fake()->optional()->bothify('R##'),
            'instructions' => fake()->optional()->sentence(),
        ];
    }
}

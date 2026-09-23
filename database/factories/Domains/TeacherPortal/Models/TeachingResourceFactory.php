<?php

namespace Database\Factories\Domains\TeacherPortal\Models;

use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\User;
use App\Domains\TeacherPortal\Models\TeachingResource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeachingResource>
 */
class TeachingResourceFactory extends Factory
{
    protected $model = TeachingResource::class;

    public function definition(): array
    {
        return [
            'teacher_id' => User::factory(),
            'class_subject_id' => ClassSubject::factory(),
            'subject_id' => Subject::factory(),
            'title' => fake()->sentence(4),
            'type' => 'document',
            'resource_path' => null,
            'external_url' => fake()->optional()->url(),
            'unit' => fake()->optional()->words(3, true),
            'visibility' => 'teacher',
            'description' => fake()->optional()->paragraph(),
        ];
    }
}

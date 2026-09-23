<?php

namespace Database\Factories\Domains\Academics\Models;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassSubject>
 */
class ClassSubjectFactory extends Factory
{
    protected $model = ClassSubject::class;

    public function definition(): array
    {
        $classRoom = ClassRoom::factory()->create();

        return [
            'class_room_id' => $classRoom->getKey(),
            'subject_id' => Subject::factory(),
            'teacher_id' => User::factory(),
            'periods_per_week' => 4,
            'position' => 1,
            'is_homeroom' => false,
        ];
    }

    public function homeroom(): static
    {
        return $this->state(fn (array $attributes) => ['is_homeroom' => true]);
    }
}

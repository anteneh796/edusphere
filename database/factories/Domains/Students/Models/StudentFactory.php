<?php

namespace Database\Factories\Domains\Students\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Students\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'student_number' => 'ES-'.fake()->unique()->numerify('####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'other_names' => fake()->boolean(30) ? fake()->firstName() : null,
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-18 years', '-6 years'),
            'status' => 'active',
            'enrollment_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'address' => fake()->optional()->address(),
            'grade_level_id' => GradeLevel::factory(),
            'class_room_id' => ClassRoom::factory(),
            'academic_year_id' => AcademicYear::factory(),
        ];
    }

    public function graduated(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'graduated']);
    }
}

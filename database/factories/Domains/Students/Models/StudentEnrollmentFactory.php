<?php

namespace Database\Factories\Domains\Students\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Students\Models\Student;
use App\Domains\Students\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentEnrollment>
 */
class StudentEnrollmentFactory extends Factory
{
    protected $model = StudentEnrollment::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'grade_level_id' => GradeLevel::factory(),
            'class_room_id' => null,
            'status' => 'active',
            'enrolled_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'left_at' => null,
        ];
    }
}

<?php

namespace Database\Factories\Domains\Exams\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Exams\Models\ExamSubject;
use App\Domains\Students\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamResult>
 */
class ExamResultFactory extends Factory
{
    protected $model = ExamResult::class;

    public function definition(): array
    {
        return [
            'exam_subject_id' => ExamSubject::factory(),
            'student_id' => Student::factory(),
            'marks_obtained' => fake()->randomFloat(2, 20, 100),
            'remarks' => fake()->optional()->sentence(),
            'entered_by_id' => User::factory(),
        ];
    }
}

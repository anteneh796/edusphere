<?php

namespace Database\Factories\Domains\Exams\Models;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\Subject;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ExamSubject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamSubject>
 */
class ExamSubjectFactory extends Factory
{
    protected $model = ExamSubject::class;

    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'class_room_id' => ClassRoom::factory(),
            'subject_id' => Subject::factory(),
            'max_marks' => 100,
            'pass_marks' => 50,
            'weight' => null,
            'exam_date' => now()->addDays(10)->toDateString(),
            'position' => 0,
            'instruction' => null,
        ];
    }
}

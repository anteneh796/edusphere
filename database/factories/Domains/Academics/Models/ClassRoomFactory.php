<?php

namespace Database\Factories\Domains\Academics\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassRoom>
 */
class ClassRoomFactory extends Factory
{
    protected $model = ClassRoom::class;

    public function definition(): array
    {
        return [
            'grade_level_id' => GradeLevel::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'name' => strtoupper(fake()->randomLetter()).'1',
            'capacity' => 40,
        ];
    }
}

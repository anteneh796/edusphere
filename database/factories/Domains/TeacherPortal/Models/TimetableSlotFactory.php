<?php

namespace Database\Factories\Domains\TeacherPortal\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\TeacherPortal\Models\TimetableSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimetableSlot>
 */
class TimetableSlotFactory extends Factory
{
    protected $model = TimetableSlot::class;

    public function definition(): array
    {
        return [
            'class_room_id' => ClassRoom::factory(),
            'class_subject_id' => null,
            'academic_year_id' => AcademicYear::factory(),
            'day_of_week' => fake()->numberBetween(1, 5),
            'period_number' => fake()->numberBetween(1, 8),
            'room' => fake()->optional()->bothify('R##'),
        ];
    }
}

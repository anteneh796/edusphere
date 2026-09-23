<?php

namespace Database\Factories\Domains\Admissions\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\User;
use App\Domains\Admissions\Models\GradeCapacity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeCapacity>
 */
class GradeCapacityFactory extends Factory
{
    protected $model = GradeCapacity::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'grade_level_id' => GradeLevel::factory(),
            'capacity' => 40,
            'allow_override' => false,
            'updated_by' => User::factory(),
        ];
    }

    public function allowOverride(): static
    {
        return $this->state(fn (array $attributes) => ['allow_override' => true]);
    }
}

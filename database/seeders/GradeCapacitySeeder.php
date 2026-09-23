<?php

namespace Database\Seeders;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Admissions\Models\GradeCapacity;
use Illuminate\Database\Seeder;

class GradeCapacitySeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::current()->first() ?? AcademicYear::latest('start_date')->first();

        if (! $year) {
            return;
        }

        foreach (GradeLevel::ordered()->get() as $grade) {
            GradeCapacity::updateOrCreate(
                [
                    'academic_year_id' => $year->getKey(),
                    'grade_level_id' => $grade->getKey(),
                ],
                [
                    'capacity' => 40,
                    'allow_override' => false,
                ]
            );
        }
    }
}

<?php

namespace Database\Factories\Domains\Academics\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        static $label = 0;

        $labels = ['A', 'B', 'C', 'D'];

        return [
            'academic_year_id' => AcademicYear::factory(),
            'name' => $labels[$label++ % count($labels)],
            'sort_order' => 0,
        ];
    }
}

<?php

namespace Database\Factories\Domains\Academics\Models;

use App\Domains\Academics\Models\AcademicTerm;
use App\Domains\Academics\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicTerm>
 */
class AcademicTermFactory extends Factory
{
    protected $model = AcademicTerm::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('this month', '+2 months');

        return [
            'academic_year_id' => AcademicYear::factory(),
            'name' => 'Term '.fake()->randomDigitNotNull(),
            'sequence' => 1,
            'start_date' => $start,
            'end_date' => (clone $start)->modify('+3 months'),
            'is_current' => false,
        ];
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Term 1',
            'sequence' => 1,
            'is_current' => true,
        ]);
    }
}

<?php

namespace Database\Factories\Domains\Academics\Models;

use App\Domains\Academics\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-3 years', '-1 year');

        return [
            'name' => $start->format('Y').'/'.($start->format('Y') + 1),
            'start_date' => $start,
            'end_date' => (clone $start)->modify('+10 months'),
            'is_current' => false,
        ];
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '2026/27',
            'start_date' => '2026-09-10',
            'end_date' => '2027-07-02',
            'is_current' => true,
        ]);
    }
}

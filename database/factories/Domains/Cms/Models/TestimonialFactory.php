<?php

namespace Database\Factories\Domains\Cms\Models;

use App\Domains\Cms\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role' => fake()->randomElement(['Parent', 'Alumna', 'Alumnus', 'Teacher', 'Guardian']),
            'quote' => fake()->paragraph(2),
            'avatar_path' => null,
            'sort_order' => 0,
            'published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => ['published' => false]);
    }
}

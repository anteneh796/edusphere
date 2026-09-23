<?php

namespace Database\Factories\Domains\Cms\Models;

use App\Domains\Cms\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3, true);
        $startsAt = fake()->dateTimeBetween('-2 months', '+4 months');

        return [
            'slug' => Str::slug($title.'-'.$startsAt->format('Y-m-d')),
            'title' => rtrim($title, '.'),
            'description' => fake()->paragraphs(2, true),
            'location' => fake()->optional()->company().' Hall',
            'starts_at' => $startsAt,
            'ends_at' => fake()->optional(0.6)->dateTimeBetween($startsAt, (clone $startsAt)->modify('+6 hours')),
            'cover_path' => null,
            'featured' => false,
            'published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => ['published' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['featured' => true]);
    }
}

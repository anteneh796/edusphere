<?php

namespace Database\Factories\Domains\Cms\Models;

use App\Domains\Cms\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'slug' => \Illuminate\Support\Str::slug($title),
            'title' => $title,
            'subtitle' => $this->faker->sentence(),
            'body' => $this->faker->paragraphs(4, true),
            'published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => ['published' => false]);
    }
}
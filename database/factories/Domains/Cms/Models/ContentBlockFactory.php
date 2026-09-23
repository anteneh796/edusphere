<?php

namespace Database\Factories\Domains\Cms\Models;

use App\Domains\Cms\Models\ContentBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentBlock>
 */
class ContentBlockFactory extends Factory
{
    protected $model = ContentBlock::class;

    public function definition(): array
    {
        return [
            'page_slug' => 'home',
            'key' => 'hero',
            'eyebrow' => fake()->word(),
            'title' => fake()->sentence(5),
            'lead' => fake()->sentence(12),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'payload' => ['items' => [
                ['icon' => 'check', 'title' => fake()->word(), 'text' => fake()->sentence()],
            ]],
            'sort_order' => 0,
            'published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => ['published' => false]);
    }
}

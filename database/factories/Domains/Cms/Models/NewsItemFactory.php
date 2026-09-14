<?php

namespace Database\Factories\Domains\Cms\Models;

use App\Domains\Cms\Models\NewsItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NewsItem>
 */
class NewsItemFactory extends Factory
{
    protected $model = NewsItem::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(4);

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'excerpt' => $this->faker->sentence(),
            'body' => $this->faker->paragraphs(3, true),
            'image_path' => null,
            'author_id' => \App\Domains\Accounts\Models\User::factory(),
            'published' => true,
            'published_at' => now(),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published' => false,
            'published_at' => null,
        ]);
    }
}
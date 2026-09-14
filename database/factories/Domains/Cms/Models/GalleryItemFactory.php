<?php

namespace Database\Factories\Domains\Cms\Models;

use App\Domains\Cms\Models\GalleryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryItem>
 */
class GalleryItemFactory extends Factory
{
    protected $model = GalleryItem::class;

    public function definition(): array
    {
        return [
            'caption' => $this->faker->optional()->sentence(5),
            'image_path' => null,
            'sort_order' => 0,
            'published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => ['published' => false]);
    }
}
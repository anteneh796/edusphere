<?php

namespace Database\Factories\Domains\Notifications\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Notifications\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'system',
            'title' => fake()->sentence(4),
            'body' => fake()->optional()->paragraph(),
            'category' => 'system',
            'priority' => 'low',
            'icon' => 'bell',
            'redirect_url' => null,
            'data' => null,
            'read_at' => null,
        ];
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => ['read_at' => null]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => ['read_at' => now()]);
    }
}

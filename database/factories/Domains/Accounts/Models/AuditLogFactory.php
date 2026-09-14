<?php

namespace Database\Factories\Domains\Accounts\Models;

use App\Domains\Accounts\Models\AuditLog;
use App\Domains\Accounts\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'deleted', 'imported', 'exported']),
            'module' => fake()->randomElement(['students', 'users', 'exams', 'attendance']),
            'record_id' => fake()->optional()->uuid(),
            'meta' => fake()->optional()->words(3),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}

<?php

namespace Database\Factories\Domains\Approvals\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Approvals\Models\ApprovalRequest;
use App\Support\Enums\ApprovalStatus;
use App\Support\Enums\ApprovalType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApprovalRequest>
 */
class ApprovalRequestFactory extends Factory
{
    protected $model = ApprovalRequest::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(ApprovalType::cases())->value,
            'status' => ApprovalStatus::Pending->value,
            'requested_by_id' => User::factory(),
            'reason' => fake()->optional()->sentence(),
            'submitted_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => ApprovalStatus::Pending->value]);
    }

    public function ofType(ApprovalType $type): static
    {
        return $this->state(fn (array $attributes) => ['type' => $type->value]);
    }
}

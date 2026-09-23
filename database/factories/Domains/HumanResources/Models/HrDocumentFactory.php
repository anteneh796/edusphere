<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\HrDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HrDocument>
 */
class HrDocumentFactory extends Factory
{
    protected $model = HrDocument::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'category' => fake()->randomElement(['employment_contract', 'degree_certificate', 'teaching_license', 'national_id']),
            'title' => fake()->words(3, true),
            'file_path' => 'hr/documents/'.fake()->unique()->word().'.pdf',
            'uploaded_by_id' => User::factory(),
            'uploaded_at' => now(),
            'verification_status' => 'pending',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => ['verification_status' => 'verified']);
    }
}
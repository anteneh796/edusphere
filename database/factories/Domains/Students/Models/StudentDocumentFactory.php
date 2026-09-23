<?php

namespace Database\Factories\Domains\Students\Models;

use App\Domains\Students\Models\Student;
use App\Domains\Students\Models\StudentDocument;
use App\Support\Enums\StudentDocumentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentDocument>
 */
class StudentDocumentFactory extends Factory
{
    protected $model = StudentDocument::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'category' => fake()->randomElement(StudentDocumentCategory::values()),
            'name' => fake()->words(3, true),
            'path' => 'student-documents/fixture.pdf',
            'mime' => 'application/pdf',
            'size' => fake()->numberBetween(1000, 500000),
            'verified' => false,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified' => true,
            'verified_at' => now(),
        ]);
    }
}

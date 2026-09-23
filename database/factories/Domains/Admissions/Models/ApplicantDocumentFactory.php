<?php

namespace Database\Factories\Domains\Admissions\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Models\ApplicantDocument;
use App\Support\Enums\AdmissionDocumentCategory;
use App\Support\Enums\DocumentVerificationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicantDocument>
 */
class ApplicantDocumentFactory extends Factory
{
    protected $model = ApplicantDocument::class;

    public function definition(): array
    {
        return [
            'application_id' => AdmissionApplication::factory(),
            'category' => fake()->randomElement(AdmissionDocumentCategory::cases())->value,
            'file_path' => 'admissions/documents/'.fake()->uuid().'.pdf',
            'original_name' => fake()->slug(2).'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(50_000, 2_000_000),
            'status' => DocumentVerificationStatus::Pending->value,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentVerificationStatus::Verified->value,
            'verified_at' => now(),
            'verified_by' => User::factory(),
        ]);
    }

    public function rejected(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentVerificationStatus::Rejected->value,
            'rejection_reason' => $reason ?? 'Blurry scan, please resubmit.',
            'verified_at' => now(),
            'verified_by' => User::factory(),
        ]);
    }
}

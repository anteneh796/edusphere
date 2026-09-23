<?php

namespace Database\Factories\Domains\Students\Models;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Students\Models\Student;
use App\Domains\Students\Models\StudentTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentTransfer>
 */
class StudentTransferFactory extends Factory
{
    protected $model = StudentTransfer::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'type' => 'internal',
            'from_class_room_id' => ClassRoom::factory(),
            'to_class_room_id' => ClassRoom::factory(),
            'transfer_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'reason' => fake()->optional()->sentence(),
        ];
    }

    public function external(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'external',
            'to_class_room_id' => null,
            'destination_school' => fake()->company().' School',
            'certificate_number' => strtoupper(fake()->bothify('TC-####-##')),
        ]);
    }
}

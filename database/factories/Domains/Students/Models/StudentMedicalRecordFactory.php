<?php

namespace Database\Factories\Domains\Students\Models;

use App\Domains\Students\Models\Student;
use App\Domains\Students\Models\StudentMedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentMedicalRecord>
 */
class StudentMedicalRecordFactory extends Factory
{
    protected $model = StudentMedicalRecord::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'blood_group' => fake()->randomElement(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-']),
            'allergies' => fake()->optional(0.6)->randomElement(['Peanuts', 'Penicillin', 'Dust', 'Latex']),
            'medical_conditions' => fake()->optional(0.5)->randomElement(['Asthma', 'Epilepsy', 'Diabetes']),
            'medications' => fake()->optional(0.4)->sentence(),
            'disability_support' => fake()->optional(0.3)->sentence(),
            'doctor_name' => fake()->optional()->name(),
            'emergency_hospital' => fake()->optional()->company(),
            'health_notes' => fake()->optional(0.4)->sentence(),
        ];
    }
}

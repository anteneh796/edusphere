<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\PayrollProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollProfile>
 */
class PayrollProfileFactory extends Factory
{
    protected $model = PayrollProfile::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'salary_grade' => fake()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1']),
            'basic_salary' => fake()->numberBetween(15000, 90000),
            'allowances' => ['transport' => fake()->numberBetween(1000, 5000)],
            'bank_name' => fake()->randomElement(['Commercial Bank of Ethiopia', 'Awash Bank', 'Dashen Bank', 'Abyssinia Bank']),
            'account_number' => fake()->numerify('################'),
            'tax_id' => fake()->numerify('#####'),
            'payment_method' => 'bank_transfer',
        ];
    }
}
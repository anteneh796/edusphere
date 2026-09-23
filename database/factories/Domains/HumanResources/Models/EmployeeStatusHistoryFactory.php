<?php

namespace Database\Factories\Domains\HumanResources\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\EmployeeStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeStatusHistory>
 */
class EmployeeStatusHistoryFactory extends Factory
{
    protected $model = EmployeeStatusHistory::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'old_status' => 'active',
            'new_status' => 'resigned',
            'changed_by_id' => User::factory(),
            'changed_at' => now(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
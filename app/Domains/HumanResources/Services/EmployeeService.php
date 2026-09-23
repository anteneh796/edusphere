<?php

namespace App\Domains\HumanResources\Services;

use App\Domains\Accounts\Models\User;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\EmployeeStatusHistory;
use App\Support\ActivityLogger;
use App\Support\Enums\EmploymentStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EmployeeService
{
    public function nextEmployeeId(): string
    {
        $prefix = 'EMP-'.now()->format('y').'-';

        $last = Employee::withTrashed()
            ->where('employee_id', 'like', "{$prefix}%")
            ->orderByDesc('employee_id')
            ->value('employee_id');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a permanent employee record. Optionally provisions a login account
     * (user) so the employee can use the staff/self-service portals.
     */
    public function create(array $data): Employee
    {
        $data['employee_id'] = $data['employee_id'] ?? $this->nextEmployeeId();

        $employee = Employee::create(Arr::except($data, ['create_account', 'password', 'roles']));

        if ($this->wantsAccount($data)) {
            $user = $this->provisionAccount($data, $employee);
            $employee->forceFill(['user_id' => $user->getKey()])->save();
        }

        $this->recordStatusHistory($employee, null, $employee->employment_status);

        ActivityLogger::log('created employee record for '.$employee->employee_id, 'hr', $employee->getKey(), [
            'employee_id' => $employee->employee_id,
        ]);

        return $employee->load('user', 'department', 'position', 'supervisor');
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update(Arr::except($data, ['create_account', 'password', 'roles', 'employee_id', 'user_id']));

        if ($this->wantsAccount($data) && $employee->user_id === null) {
            $user = $this->provisionAccount($data, $employee);
            $employee->forceFill(['user_id' => $user->getKey()])->save();
        }

        ActivityLogger::log('updated employee record for '.$employee->employee_id, 'hr', $employee->getKey());

        return $employee->load('user', 'department', 'position', 'supervisor');
    }

    /**
     * Change employment status, keeping a permanent status history trail. The
     * record itself is never deleted.
     */
    public function changeStatus(Employee $employee, EmploymentStatus $newStatus, ?string $notes = null, ?User $changedBy = null): Employee
    {
        $oldStatus = $employee->employment_status;

        if ($oldStatus === $newStatus->value) {
            return $employee;
        }

        $employee->update(['employment_status' => $newStatus->value]);

        $this->recordStatusHistory($employee, $oldStatus, $newStatus->value, $notes, $changedBy);

        ActivityLogger::log('changed '.$employee->employee_id.' status to '.$newStatus->value, 'hr', $employee->getKey(), [
            'old_status' => $oldStatus,
            'new_status' => $newStatus->value,
        ]);

        return $employee;
    }

    private function wantsAccount(array $data): bool
    {
        return (bool) ($data['create_account'] ?? false) && filled($data['email'] ?? null);
    }

    private function provisionAccount(array $data, Employee $employee): User
    {
        $parts = preg_split('/\s+/', trim($data['full_name'] ?? $employee->full_name)) ?: [];
        $first = $parts[0] ?? $employee->full_name;
        $last = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : $first;

        $user = User::create([
            'first_name' => $first,
            'last_name' => $last,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'employee_id' => $employee->employee_id,
            'job_title' => $employee->position?->name ?? null,
            'department' => $employee->department?->name ?? null,
            'hire_date' => $employee->joining_date,
            'password' => $data['password'] ?? Str::password(10),
            'status' => 'active',
            'email_verified_at' => now(),
            'must_change_password' => true,
        ]);

        $roles = $data['roles'] ?? null;

        if (is_array($roles) && $roles !== []) {
            $user->roles()->sync($roles);
        }

        return $user;
    }

    private function recordStatusHistory(Employee $employee, ?string $oldStatus, string $newStatus, ?string $notes = null, ?User $changedBy = null): void
    {
        EmployeeStatusHistory::create([
            'employee_id' => $employee->getKey(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by_id' => $changedBy?->getKey() ?? auth()->id(),
            'changed_at' => now(),
            'notes' => $notes,
        ]);
    }
}
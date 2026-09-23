<?php

namespace App\Domains\Accounts\Requests;

use App\Domains\Accounts\Services\PasswordService;
use App\Support\Enums\RoleName;
use App\Support\Enums\StaffType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->getKey();

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email,'.$userId],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s-]+$/'],
            'employee_id' => ['nullable', 'string', 'max:30', 'unique:users,employee_id,'.$userId],
            'staff_type' => ['nullable', 'string', Rule::enum(StaffType::class)],
            'department' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'hire_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'password' => ['nullable', 'confirmed', ...PasswordService::policyRules()],
            'status' => ['required', 'in:active,inactive,suspended,archived'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [$this->staffRoleRule()],
        ];
    }

    private function staffRoleRule(): Exists
    {
        return Rule::exists('roles', 'id')->whereNotIn('name', [RoleName::Student->value, RoleName::Parent->value]);
    }

    public function attributes(): array
    {
        return [
            'employee_id' => 'employee ID',
            'staff_type' => 'staff type',
        ];
    }
}

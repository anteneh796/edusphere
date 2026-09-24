<?php

namespace Tests\Feature;

use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\EmployeeStatusHistory;
use App\Domains\HumanResources\Models\EmploymentContract;
use App\Domains\HumanResources\Models\LeaveRequest;
use App\Domains\HumanResources\Models\LeaveType;
use App\Domains\HumanResources\Models\StaffAttendance;
use App\Support\Enums\EmploymentStatus;
use App\Support\Enums\LeaveRequestStatus;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrModuleTest extends TestCase
{
    use RefreshDatabase;

    private function role(string $name): Role
    {
        return Role::create(['name' => $name, 'label' => $name]);
    }

    private function user(string $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role($roleName));

        return $user;
    }

    public function test_hr_pages_require_hr_permission(): void
    {
        $teacher = $this->user(RoleName::Teacher->value);

        $this->actingAs($teacher)
            ->get(route('hr.dashboard'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('hr.employees.index'))
            ->assertForbidden();
    }

    public function test_hr_officer_can_create_employee_with_linked_account(): void
    {
        $hr = $this->user(RoleName::HROfficer->value);
        $teacherRole = $this->role(RoleName::Teacher->value);

        $this->actingAs($hr)
            ->post(route('hr.employees.store'), [
                'full_name' => 'Samson Alemu',
                'email' => 'samson.alemu@edusphere.com',
                'gender' => 'male',
                'employment_type' => 'full_time',
                'employment_status' => 'active',
                'joining_date' => '2024-09-01',
                'create_account' => true,
                'roles' => [$teacherRole->getKey()],
            ])
            ->assertRedirect();

        $employee = Employee::where('email', 'samson.alemu@edusphere.com')->first();

        $this->assertNotNull($employee, 'Employee should exist.');
        $this->assertNotNull($employee->user_id, 'Employee should be linked to a login account.');
        $this->assertStringStartsWith('EMP-', $employee->employee_id);
        $this->assertTrue($employee->user->roles->contains('id', $teacherRole->getKey()));
    }

    public function test_employee_status_change_is_tracked_permanently(): void
    {
        $schoolAdmin = $this->user(RoleName::SchoolAdmin->value);
        $employee = Employee::factory()->create(['employment_status' => 'active']);

        $this->actingAs($schoolAdmin)
            ->put(route('hr.employees.status', $employee), ['employment_status' => 'suspended'])
            ->assertRedirect(route('hr.employees.show', $employee));

        $this->assertSame('suspended', $employee->fresh()->employment_status);

        $history = EmployeeStatusHistory::where('employee_id', $employee->getKey())->latest()->first();

        $this->assertNotNull($history);
        $this->assertSame('active', $history->old_status);
        $this->assertSame('suspended', $history->new_status);
    }

    public function test_leave_request_approval_flow(): void
    {
        $principal = $this->user(RoleName::Principal->value);
        $employee = Employee::factory()->create();
        $leaveType = LeaveType::factory()->create();

        $this->actingAs($principal)
            ->post(route('hr.leave.store.for', $employee), [
                'leave_type_id' => $leaveType->getKey(),
                'start_date' => '2026-10-05',
                'end_date' => '2026-10-07',
                'days' => 3,
                'reason' => 'Family event',
            ])
            ->assertRedirect();

        $leaveRequest = LeaveRequest::where('employee_id', $employee->getKey())->first();

        $this->assertNotNull($leaveRequest);

        $this->actingAs($principal)
            ->post(route('hr.leave.review', $leaveRequest), [
                'action' => 'approved',
                'note' => 'Approved with pay.',
            ])
            ->assertRedirect(route('hr.leave.show', $leaveRequest));

        $leaveRequest->refresh();

        $this->assertSame(LeaveRequestStatus::Approved->value, $leaveRequest->status);
        $this->assertSame($principal->getKey(), $leaveRequest->reviewed_by_id);
        $this->assertNotNull($leaveRequest->reviewed_at);
    }

    public function test_attendance_marking_is_unique_per_employee_and_date(): void
    {
        $schoolAdmin = $this->user(RoleName::SchoolAdmin->value);
        $employee = Employee::factory()->create();
        $date = '2026-09-22';

        $this->actingAs($schoolAdmin)
            ->post(route('hr.attendance.store'), [
                'date' => $date,
                'statuses' => [$employee->getKey() => 'present'],
            ])
            ->assertRedirect(route('hr.attendance.index', ['date' => $date]));

        $this->actingAs($schoolAdmin)
            ->post(route('hr.attendance.store'), [
                'date' => $date,
                'statuses' => [$employee->getKey() => 'late'],
            ])
            ->assertRedirect(route('hr.attendance.index', ['date' => $date]));

        $rows = StaffAttendance::where('employee_id', $employee->getKey())
            ->whereDate('attendance_date', $date)
            ->get();

        $this->assertCount(1, $rows, 'Attendance should be upserted, not duplicated.');
        $this->assertSame('late', $rows->first()->status);
    }

    public function test_contract_can_be_renewed_with_new_end_date(): void
    {
        $hr = $this->user(RoleName::HROfficer->value);
        $contract = EmploymentContract::factory()->create();

        $this->actingAs($hr)
            ->post(route('hr.contracts.renew', $contract), [
                'renewal_status' => 'renewed',
                'new_end_date' => '2027-12-31',
            ])
            ->assertRedirect(route('hr.contracts.show', $contract));

        $contract->refresh();

        $this->assertSame('2027-12-31', $contract->end_date->toDateString());
        $this->assertSame('active', $contract->renewal_status);
    }

    public function test_payroll_is_restricted_but_reports_are_role_gated(): void
    {
        $principal = $this->user(RoleName::Principal->value);
        $hr = $this->user(RoleName::HROfficer->value);

        $this->actingAs($principal)
            ->get(route('hr.payroll.index'))
            ->assertForbidden();

        $this->actingAs($principal)
            ->get(route('hr.reports.employees'))
            ->assertOk();

        $this->actingAs($hr)
            ->get(route('hr.payroll.index'))
            ->assertOk();

        $this->actingAs($principal)
            ->get(route('hr.payroll.index'))
            ->assertForbidden();

        $principal = Role::where('name', RoleName::Principal->value)->first();
        $this->assertTrue($principal->permissions->pluck('name')->contains('hr.leave.approve'));
        $this->assertFalse($principal->permissions->pluck('name')->contains('hr.payroll'));
    }

    public function test_employee_records_are_never_hard_deleted(): void
    {
        $schoolAdmin = $this->user(RoleName::SchoolAdmin->value);
        $employee = Employee::factory()->create();

        $this->actingAs($schoolAdmin)
            ->delete(route('hr.employees.destroy', $employee))
            ->assertRedirect(route('hr.employees.index'));

        $employee->refresh();
        $this->assertSame(EmploymentStatus::Terminated->value, $employee->employment_status);
        $this->assertNotNull(Employee::find($employee->getKey()), 'Archived employee records must remain queryable.');
    }
}
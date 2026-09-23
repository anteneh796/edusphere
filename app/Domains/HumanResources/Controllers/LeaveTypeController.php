<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\HumanResources\Models\LeaveType;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('hr.view');

        $leaveTypes = LeaveType::withCount([
            'leaveRequests' => fn ($query) => $query->whereYear('created_at', now()->year),
            'leaveRequests as approved_count' => fn ($query) => $query->whereYear('created_at', now()->year)->where('status', 'approved'),
        ])
            ->orderBy('name')
            ->paginate(15);

        return view('hr.leave-types.index', compact('leaveTypes'));
    }

    public function create(): View
    {
        $this->requirePermission('hr.create');

        return view('hr.leave-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('hr.create');

        $leaveType = LeaveType::create($this->validated($request));

        ActivityLogger::log('created leave type '.$leaveType->name, 'hr', $leaveType->getKey());

        return redirect()->route('hr.leave-types.index')->with('status', 'Leave type "'.$leaveType->name.'" created.');
    }

    public function edit(LeaveType $leaveType): View
    {
        $this->requirePermission('hr.view');

        return view('hr.leave-types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $this->requirePermission('hr.view');

        $leaveType->update($this->validated($request, $leaveType));

        ActivityLogger::log('updated leave type '.$leaveType->name, 'hr', $leaveType->getKey());

        return redirect()->route('hr.leave-types.index')->with('status', 'Leave type updated.');
    }

    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        $this->requirePermission('hr.delete');

        if ($leaveType->leaveRequests()->exists()) {
            return back()->withErrors(['leave_type' => 'Cannot delete a leave type that is already in use.']);
        }

        $leaveType->delete();

        ActivityLogger::log('deleted leave type '.$leaveType->name, 'hr', null);

        return redirect()->route('hr.leave-types.index')->with('status', 'Leave type deleted.');
    }

    private function validated(Request $request, LeaveType $leaveType = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('leave_types', 'name')->ignore($leaveType?->getKey())],
            'code' => ['required', 'string', 'max:20', Rule::unique('leave_types', 'code')->ignore($leaveType?->getKey())],
            'days_per_year' => ['required', 'integer', 'min:0', 'max:365'],
            'is_paid' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_paid'] = $request->boolean('is_paid');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}
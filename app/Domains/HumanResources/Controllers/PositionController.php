<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\HumanResources\Models\Department;
use App\Domains\HumanResources\Models\Position;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PositionController extends Controller
{
    public const CATEGORIES = [
        'executive' => 'Executive',
        'academic' => 'Academic',
        'student_support' => 'Student Support',
        'finance' => 'Finance',
        'hr' => 'Human Resources',
        'it' => 'IT & Systems',
        'administration' => 'Administration',
        'operations' => 'Operations',
    ];

    public function index(): View
    {
        $this->requirePermission('hr.view');

        $positions = Position::query()
            ->with('department:id,name')
            ->withCount('employees')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(15);

        return view('hr.positions.index', compact('positions'));
    }

    public function create(): View
    {
        $this->requirePermission('hr.create');

        $departments = Department::orderBy('name')->get();

        return view('hr.positions.create', ['departments' => $departments, 'categories' => self::CATEGORIES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('hr.create');

        $position = Position::create($this->validated($request));

        ActivityLogger::log('created position '.$position->name, 'hr', $position->getKey());

        return redirect()->route('hr.positions.index')->with('status', 'Position "'.$position->name.'" created.');
    }

    public function edit(Position $position): View
    {
        $this->requirePermission('hr.edit');

        $departments = Department::orderBy('name')->get();
        $categories = self::CATEGORIES;

        return view('hr.positions.edit', compact('position', 'departments', 'categories'));
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $this->requirePermission('hr.edit');

        $position->update($this->validated($request, $position));

        ActivityLogger::log('updated position '.$position->name, 'hr', $position->getKey());

        return redirect()->route('hr.positions.index')->with('status', 'Position "'.$position->name.'" updated.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $this->requirePermission('hr.delete');

        if ($position->employees()->exists()) {
            return back()->withErrors(['position' => 'Cannot delete a position that still has assigned staff.']);
        }

        $position->delete();

        ActivityLogger::log('deleted position '.$position->name, 'hr', $position->getKey());

        return redirect()->route('hr.positions.index')->with('status', 'Position deleted.');
    }

    private function validated(Request $request, Position $position = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('positions', 'name')->ignore($position?->getKey())],
            'slug' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('positions', 'slug')->ignore($position?->getKey())],
            'category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'department_id' => ['nullable', 'exists:departments,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}
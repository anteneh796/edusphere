<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\OfficialLetter;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\OfficialLetterType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfficialLetterController extends Controller
{
    public function index(Request $request): View
    {
        $this->requirePermission('hr.view');

        $letters = OfficialLetter::query()
            ->with(['employee:id,full_name,employee_id,date_of_birth'])
            ->when($request->filled('type') && OfficialLetterType::tryFrom($request->query('type')), function ($query) use ($request) {
                $query->where('letter_type', $request->query('type'));
            })
            ->latest('issued_on')
            ->paginate(15)
            ->withQueryString();

        return view('hr.letters.index', compact('letters'));
    }

    public function create(): View
    {
        $this->requirePermission('hr.create');

        $employees = Employee::whereIn('employment_status', ['active', 'probation'])->orderBy('full_name')->get(['id', 'full_name', 'employee_id']);
        $nextReference = OfficialLetter::nextReferenceNumber();

        return view('hr.letters.create', compact('employees', 'nextReference'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('hr.create');

        $validated = $request->validate([
            'reference_number' => ['nullable', 'string', 'max:50'],
            'employee_id' => ['required', 'exists:employees,id'],
            'letter_type' => ['required', Rule::enum(OfficialLetterType::class)],
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:20000'],
            'issued_on' => ['required', 'date'],
        ]);

        $validated['reference_number'] = $validated['reference_number'] ?? OfficialLetter::nextReferenceNumber();
        $validated['issued_by_id'] = $request->user()->getKey();

        $letter = OfficialLetter::create($validated);

        ActivityLogger::log('issued official letter "'.$letter->title.'"', 'hr', $letter->getKey());

        return redirect()
            ->route('hr.letters.show', $letter)
            ->with('status', 'Official letter issued.');
    }

    public function show(OfficialLetter $letter): View
    {
        $this->requirePermission('hr.view');

        $letter->load(['employee:id,full_name,employee_id,date_of_birth,joining_date,position_id,department_id', 'issuedBy:id,first_name,last_name']);

        return view('hr.letters.show', compact('letter'));
    }

    public function destroy(OfficialLetter $letter): RedirectResponse
    {
        $this->requirePermission('hr.delete');

        $letter->delete();

        ActivityLogger::log('deleted official letter "'.$letter->title.'"', 'hr', null);

        return redirect()->route('hr.letters.index')->with('status', 'Official letter deleted.');
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}
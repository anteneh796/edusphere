<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\HrDocument;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\DocumentVerificationStatus;
use App\Support\Enums\HrDocumentCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $this->requirePermission('hr.view');

        $documents = HrDocument::query()
            ->with(['employee:id,full_name,employee_id', 'uploadedBy:id,first_name,last_name'])
            ->when($request->filled('category') && HrDocumentCategory::tryFrom($request->query('category')), function ($query) use ($request) {
                $query->where('category', $request->query('category'));
            })
            ->when($request->filled('status'), fn ($query, $status) => $query->where('verification_status', $status))
            ->latest('uploaded_at')
            ->paginate(15)
            ->withQueryString();

        $pendingCount = HrDocument::where('verification_status', DocumentVerificationStatus::Pending->value)->count();

        return view('hr.documents.index', compact('documents', 'pendingCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('hr.create');

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'category' => ['required', Rule::enum(HrDocumentCategory::class)],
            'title' => ['required', 'string', 'max:150'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['file_path'] = $request->file('document')->store('hr/documents/'.$validated['employee_id'], 'public');
        $validated['uploaded_by_id'] = $request->user()->getKey();
        $validated['uploaded_at'] = now();
        $validated['verification_status'] = DocumentVerificationStatus::Pending->value;

        $document = HrDocument::create($validated);

        ActivityLogger::log('uploaded document "'.$document->title.'"', 'hr', $document->getKey());

        return redirect()
            ->route('hr.employees.show', ['employee' => $validated['employee_id']])
            ->with('status', 'Document uploaded and queued for verification.');
    }

    public function verify(Request $request, HrDocument $document): RedirectResponse
    {
        $this->requirePermission('hr.edit');

        $validated = $request->validate([
            'verification_status' => ['required', Rule::enum(DocumentVerificationStatus::class)],
            'verified_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $document->update([
            'verification_status' => $validated['verification_status'],
            'verified_note' => $validated['verified_note'] ?? null,
            'verified_by_id' => $request->user()->getKey(),
            'verified_at' => now(),
        ]);

        ActivityLogger::log('verified document "'.$document->title.'" as '.$validated['verification_status'], 'hr', $document->getKey());

        return back()->with('status', 'Document marked '.str_replace('_', ' ', $validated['verification_status']).'.');
    }

    public function download(HrDocument $document)
    {
        $this->requirePermission('hr.view');

        abort_unless(Storage::disk('public')->exists($document->file_path), 404, 'Document file is missing.');

        return Storage::disk('public')->download($document->file_path, Str::slug($document->title).'.'.pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function destroy(HrDocument $document): RedirectResponse
    {
        $this->requirePermission('hr.delete');

        if ($document->verification_status === DocumentVerificationStatus::Verified->value) {
            return back()->withErrors(['document' => 'Verified documents cannot be deleted. Archive or flag them instead.']);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        ActivityLogger::log('deleted document "'.$document->title.'"', 'hr', null);

        return back()->with('status', 'Document deleted.');
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}
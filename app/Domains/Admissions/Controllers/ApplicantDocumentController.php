<?php

namespace App\Domains\Admissions\Controllers;

use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Models\ApplicantDocument;
use App\Domains\Admissions\Requests\StoreApplicantDocumentRequest;
use App\Domains\Admissions\Requests\VerifyApplicantDocumentRequest;
use App\Domains\Notifications\Services\NotificationService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\AdmissionDocumentCategory;
use App\Support\Enums\DocumentVerificationStatus;
use App\Support\Enums\RoleName;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ApplicantDocumentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AdmissionApplication::class);

        $documents = ApplicantDocument::with(['application.gradeLevel'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admissions.documents.index', [
            'documents' => $documents,
            'categories' => AdmissionDocumentCategory::cases(),
            'statusOptions' => DocumentVerificationStatus::cases(),
        ]);
    }

    public function store(StoreApplicantDocumentRequest $request, AdmissionApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $file = $request->file('document');
        $path = $file->store('admissions/documents', 'public');

        $application->documents()->create([
            'category' => $request->validated('category'),
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => DocumentVerificationStatus::Pending->value,
        ]);

        ActivityLogger::log('uploaded '.$request->validated('category').' for application '.$application->application_number, 'admissions', $application->id);

        return back()->with('status', 'Document uploaded.');
    }

    public function verify(VerifyApplicantDocumentRequest $request, ApplicantDocument $document): RedirectResponse
    {
        $this->authorize('verify', $document);

        $document->update([
            'status' => $request->validated('status'),
            'rejection_reason' => in_array($request->validated('status'), [
                DocumentVerificationStatus::Rejected->value,
                DocumentVerificationStatus::ReplacementRequired->value,
            ], true) ? $request->validated('rejection_reason') : null,
            'notes' => $request->validated('notes'),
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        ActivityLogger::log("marked {$document->categoryLabel()} as {$document->statusLabel()}", 'admissions', $document->application_id);

        if ($request->validated('status') === DocumentVerificationStatus::Verified->value) {
            $application = $document->application;
            app(NotificationService::class)->sendToRoles(
                [RoleName::Registrar->value],
                [
                    'type' => 'document',
                    'category' => 'admissions',
                    'priority' => 'low',
                    'icon' => 'file-text',
                    'title' => __('Document verified'),
                    'body' => __(':category verified for :name.', [
                        'category' => $document->categoryLabel(),
                        'name' => $application->full_name,
                    ]),
                    'redirect_url' => route('admissions.applications.show', $application),
                ]
            );
        }

        return back()->with('status', 'Document marked as '.$document->statusLabel().'.');
    }

    public function destroy(ApplicantDocument $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('status', 'Document removed.');
    }
}

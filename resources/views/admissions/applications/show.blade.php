<x-layouts.app :title="$application->full_name">
    <x-breadcrumb :items="[
        ['label' => __('Admissions'), 'url' => route('admissions.dashboard')],
        ['label' => __('Applications'), 'url' => route('admissions.applications.index')],
        ['label' => $application->full_name],
    ]" />

    <x-page-header :title="$application->full_name" :description="$application->application_number.' · '.($application->gradeLevel->name ?? __('Grade not set'))">
        <x-badge :color="$application->statusBadgeColor()">
            {{ $application->statusLabel() }}
        </x-badge>
    </x-page-header>

    {{-- Actions --}}
    <div class="flex gap-1 flex-wrap" style="margin-bottom: var(--space-3);">
        @can('update', $application)
            <a href="{{ route('admissions.applications.edit', $application) }}" class="btn btn-secondary btn-sm">
                <x-icon name="pencil" class="icon-sm" />
                {{ __('Edit') }}
            </a>
        @endcan

        @if (in_array($application->status, ['inquiry', 'draft'], true) && auth()->user()?->can('submit', $application))
            <form method="POST" action="{{ route('admissions.applications.submit', $application) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('{{ __('Submit this application for review?') }}')">
                    <x-icon name="send" class="icon-sm" />
                    {{ __('Submit for review') }}
                </button>
            </form>
        @endif

        @if ($application->status === 'submitted' && auth()->user()?->can('review', $application))
            <form method="POST" action="{{ route('admissions.applications.review', $application) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <x-icon name="eye" class="icon-sm" />
                    {{ __('Start review') }}
                </button>
            </form>
        @endif

        @if (in_array($application->status, ['submitted', 'under_review'], true) && auth()->user()?->can('scheduleAssessment', $application))
            <x-modal title="{{ __('Schedule assessment') }}">
                <x-slot:trigger>
                    <button type="button" class="btn btn-secondary btn-sm">
                        <x-icon name="calendar" class="icon-sm" />
                        {{ __('Schedule assessment') }}
                    </button>
                </x-slot:trigger>

                <form method="POST" action="{{ route('admissions.assessments.store', $application) }}">
                    @csrf
                    <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                        <div class="form-group">
                            <label class="form-label" for="assessment-type">{{ __('Type') }} <span class="required">*</span></label>
                            <select id="assessment-type" name="type" class="form-select" required>
                                @foreach ($assessmentTypes ?? \App\Support\Enums\AdmissionAssessmentType::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="assessment-when">{{ __('Scheduled at') }} <span class="required">*</span></label>
                            <input id="assessment-when" type="datetime-local" name="scheduled_at" class="form-control" required />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="assessment-location">{{ __('Location') }}</label>
                        <input id="assessment-location" type="text" name="location" class="form-control" placeholder="{{ __('Optional') }}" />
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">{{ __('Schedule') }}</button>
                    </div>
                </form>
            </x-modal>
        @endif

        @if (in_array($application->status, ['submitted', 'under_review', 'assessment_scheduled'], true) && auth()->user()?->can('submitForApproval', $application))
            <x-modal title="{{ __('Submit for approval') }}">
                <x-slot:trigger>
                    <button type="button" class="btn btn-primary btn-sm">
                        <x-icon name="check-circle" class="icon-sm" />
                        {{ __('Submit for approval') }}
                    </button>
                </x-slot:trigger>

                <form method="POST" action="{{ route('admissions.applications.approval', $application) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="approval-comment">{{ __('Notes') }}</label>
                        <textarea id="approval-comment" name="comment" class="form-control" rows="3" placeholder="{{ __('Optional hand-off notes for the approver') }}"></textarea>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">{{ __('Send for approval') }}</button>
                    </div>
                </form>
            </x-modal>
        @endif

        @if (in_array($application->status, ['under_review', 'pending_approval'], true) && auth()->user()?->can('decide', $application))
            <x-modal title="{{ __('Admission decision') }}" size="md">
                <x-slot:trigger>
                    <button type="button" class="btn btn-success btn-sm">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Make decision') }}
                    </button>
                </x-slot:trigger>

                <form method="POST" action="{{ route('admissions.applications.decide', $application) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="decision-select">{{ __('Decision') }} <span class="required">*</span></label>
                        <select id="decision-select" name="decision" class="form-select" required>
                            <option value="approve">{{ __('Approve') }}</option>
                            <option value="reject">{{ __('Reject') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="decision-comment">{{ __('Comment') }}</label>
                        <textarea id="decision-comment" name="comment" class="form-control" rows="3" placeholder="{{ __('Optional') }}"></textarea>
                    </div>
                    <label class="form-check">
                        <input type="checkbox" name="force" value="1" />
                        <span>{{ __('Override grade capacity') }}</span>
                    </label>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">{{ __('Record decision') }}</button>
                    </div>
                </form>
            </x-modal>
        @endif

        @if (in_array($application->status, ['pending_approval', 'under_review'], true) && auth()->user()?->can('waitlist', $application))
            <form method="POST" action="{{ route('admissions.applications.waitlist', $application) }}">
                @csrf
                <input type="hidden" name="comment" value="{{ __('Placed on the waiting list due to grade capacity.') }}" />
                <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('{{ __('Place this candidate on the waiting list?') }}')">
                    <x-icon name="users" class="icon-sm" />
                    {{ __('Waitlist') }}
                </button>
            </form>
        @endif

        @if ($application->status === 'approved' && auth()->user()?->can('enroll', $application))
            <form method="POST" action="{{ route('admissions.applications.enroll', $application) }}">
                @csrf
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('{{ __('Enroll :name now? A student record will be created.', ['name' => $application->full_name]) }}')">
                    <x-icon name="graduation" class="icon-sm" />
                    {{ __('Enroll candidate') }}
                </button>
            </form>
        @endif

        @if ($application->status === 'waitlisted' && auth()->user()?->can('promote', $application))
            <form method="POST" action="{{ route('admissions.applications.promote', $application) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('{{ __('Promote this candidate to approved?') }}')">
                    <x-icon name="check-circle" class="icon-sm" />
                    {{ __('Promote to approved') }}
                </button>
            </form>
        @endif

        @if (in_array($application->status, ['waitlisted', 'pending_approval'], true) && auth()->user()?->can('decide', $application))
            <form method="POST" action="{{ route('admissions.applications.decide', $application) }}">
                @csrf
                <input type="hidden" name="decision" value="reject" />
                <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('{{ __('Reject this application?') }}')">
                    <x-icon name="x" class="icon-sm" />
                    {{ __('Reject') }}
                </button>
            </form>
        @endif

        @if (auth()->user()?->can('withdraw', $application))
            <form method="POST" action="{{ route('admissions.applications.withdraw', $application) }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm" onclick="return confirm('{{ __('Withdraw this application?') }}')">
                    <x-icon name="arrow-left" class="icon-sm" />
                    {{ __('Withdraw') }}
                </button>
            </form>
        @endif

        @can('delete', $application)
            <button type="button" class="btn btn-ghost btn-sm text-danger"
                @click="$store.confirm.ask({
                    title: @js(__('Archive application?')),
                    message: @js(__('Archive').' '.$application->full_name.' ('.$application->application_number.'). '.__('This cannot be undone.')),
                    action: @js(route('admissions.applications.destroy', $application)),
                    method: 'DELETE',
                    confirmText: @js(__('Archive'))
                })">
                <x-icon name="trash" class="icon-sm" />
                {{ __('Archive') }}
            </button>
        @endcan
    </div>

    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-3); align-items:start;">

        @if ($application->status === 'enrolled' && $application->student)
            <x-card :title="__('Enrolled student')" style="grid-column: 1 / -1;">
                <div class="flex gap-2" style="align-items:center;">
                    <x-avatar :initials="$application->student->initials()" size="sm" />
                    <div>
                        <a href="{{ route('students.show', $application->student) }}" style="font-weight: var(--weight-semibold); color: var(--color-text);">{{ $application->student->full_name }}</a>
                        <div class="text-xs text-muted">
                            {{ $application->student->student_number }} · {{ $application->student->gradeLevel->name ?? '—' }}
                            @if ($application->student->classRoom)
                                · {{ $application->student->classRoom->name }}
                            @endif
                        </div>
                    </div>
                </div>
            </x-card>
        @endif

        {{-- Applicant details --}}
        <x-card :title="__('Applicant details')">
            <dl class="detail-list">
                <div><dt>{{ __('Full name') }}</dt><dd>{{ $application->full_name }}</dd></div>
                <div><dt>{{ __('Gender') }}</dt><dd>{{ ucfirst($application->gender ?? '—') }}</dd></div>
                <div><dt>{{ __('Date of birth') }}</dt><dd>{{ optional($application->date_of_birth)->format('M j, Y') ?? '—' }}</dd></div>
                <div><dt>{{ __('National ID') }}</dt><dd>{{ $application->national_id ?? '—' }}</dd></div>
                <div><dt>{{ __('Address') }}</dt><dd>{{ $application->address ?? '—' }}</dd></div>
                <div><dt>{{ __('Previous school') }}</dt><dd>{{ $application->previous_school ?? '—' }}</dd></div>
                <div><dt>{{ __('Intake year') }}</dt><dd>{{ $application->intakeYear->name ?? __('Current year') }}</dd></div>
                <div><dt>{{ __('Grade') }}</dt><dd>{{ $application->gradeLevel->name ?? '—' }}</dd></div>
                @if ($gradeCapacity !== null)
                    <div>
                        <dt>{{ __('Grade capacity') }}</dt>
                        <dd>{{ $seatsTaken }} / {{ $gradeCapacity }}
                            <span class="text-xs text-muted">({{ $gradeCapacity > 0 ? round(min($seatsTaken / $gradeCapacity, 1) * 100) : 0 }}% full)</span>
                        </dd>
                    </div>
                @endif
                <div><dt>{{ __('Applied') }}</dt><dd>{{ optional($application->applied_at)->format('M j, Y') ?? '—' }}</dd></div>
                <div><dt>{{ __('Source') }}</dt><dd>{{ $application->sourceInquiry ? __('Website inquiry') : __('Walk-in / other') }}</dd></div>
                @if ($application->decision_comment)
                    <div><dt>{{ __('Decision comment') }}</dt><dd class="text-sm">{{ $application->decision_comment }}</dd></div>
                @endif
                @if ($application->decisionBy)
                    <div><dt>{{ __('Decided by') }}</dt><dd>{{ $application->decisionBy->full_name }}</dd></div>
                @endif
                <div><dt>{{ __('Created by') }}</dt><dd>{{ $application->createdBy?->full_name ?? '—' }}</dd></div>
            </dl>
        </x-card>

        {{-- Parents --}}
        <x-card :title="__('Parents')">
            <x-slot:actions>
                @can('update', $application)
                    <x-modal title="{{ __('Add parent') }}">
                        <x-slot:trigger>
                            <button type="button" class="btn btn-ghost btn-sm">
                                <x-icon name="plus" class="icon-sm" />
                            </button>
                        </x-slot:trigger>
                        <form method="POST" action="{{ route('admissions.parents.store', $application) }}">
                            @csrf
                            @include('admissions.applications._parent-fields')
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">{{ __('Add parent') }}</button>
                            </div>
                        </form>
                    </x-modal>
                @endcan
            </x-slot:actions>

            @forelse ($application->parents as $parent)
                <div class="list-row">
                    <div class="avatar-cell">
                        <x-avatar :initials="strtoupper(substr($parent->first_name, 0, 1).substr($parent->last_name, 0, 1))" size="sm" />
                        <div style="min-width:0;">
                            <div style="font-weight: var(--weight-semibold);">
                                {{ $parent->full_name }}
                                @if ($parent->is_primary)
                                    <x-badge color="primary">{{ __('Primary') }}</x-badge>
                                @endif
                            </div>
                            <div class="text-xs text-muted">
                                {{ \Illuminate\Support\Str::headline($parent->relationship) }}
                                @if ($parent->is_emergency)
                                    · {{ __('Emergency contact') }}
                                @endif
                            </div>
                            <div class="text-xs text-muted">
                                {{ $parent->phone ?? '' }}
                                {{ $parent->phone && $parent->email ? '·' : '' }}
                                {{ $parent->email ?? '' }}
                            </div>
                        </div>
                    </div>
                    @can('update', $application)
                        <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" :title="__('Remove')"
                            @click="$store.confirm.ask({
                                title: @js(__('Remove parent?')),
                                message: @js($parent->full_name.' '.__('will be removed from this application.')),
                                action: @js(route('admissions.parents.destroy', [$application, $parent])),
                                method: 'DELETE'
                            })">
                            <x-icon name="trash" class="icon-sm" />
                        </button>
                    @endcan
                </div>
            @empty
                <x-empty-state icon="users" :title="__('No parents')" :message="__('Add a parent for this applicant.')" />
            @endforelse
        </x-card>

        {{-- Documents --}}
        <x-card :title="__('Documents')">
            <x-slot:actions>
                @can('update', $application)
                    <x-modal title="{{ __('Upload document') }}">
                        <x-slot:trigger>
                            <button type="button" class="btn btn-ghost btn-sm">
                                <x-icon name="upload" class="icon-sm" />
                            </button>
                        </x-slot:trigger>
                        <form method="POST" action="{{ route('admissions.documents.store', $application) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label class="form-label" for="doc-category">{{ __('Document type') }} <span class="required">*</span></label>
                                <select id="doc-category" name="category" class="form-select" required>
                                    @foreach ($documentCategories ?? \App\Support\Enums\AdmissionDocumentCategory::cases() as $category)
                                        <option value="{{ $category->value }}">{{ $category->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="doc-file">{{ __('File') }} <span class="required">*</span></label>
                                <input id="doc-file" type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" required />
                                <div class="form-hint">{{ __('PDF, JPG, PNG, WEBP · up to 5 MB') }}</div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                            </div>
                        </form>
                    </x-modal>
                @endcan
            </x-slot:actions>

            @forelse ($application->documents as $document)
                <div class="list-row">
                    <div style="min-width:0;">
                        <div style="font-weight: var(--weight-semibold);">
                            {{ $document->categoryLabel() }}
                        </div>
                        <div class="text-xs text-muted">{{ $document->original_name ?? $document->file_path }}</div>
                        <div class="text-xs text-muted">
                            {{ $document->size ? round($document->size / 1024).' KB' : '' }}
                            @if ($document->verified_at)
                                · {{ __('Verified') }} {{ $document->verified_at->format('M j, Y') }}
                                @if ($document->verifier)
                                    {{ $document->verifier->full_name }}
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-1" style="align-items:center;">
                        <x-badge :color="$document->statusBadgeColor()">{{ $document->statusLabel() }}</x-badge>
                        @can('verify', $document)
                            <x-modal title="{{ __('Verify document') }}">
                                <x-slot:trigger>
                                    <button type="button" class="btn btn-ghost btn-sm btn-icon" :title="__('Verify')">
                                        <x-icon name="check" class="icon-sm" />
                                    </button>
                                </x-slot:trigger>
                                <form method="POST" action="{{ route('admissions.documents.verify', $document) }}">
                                    @csrf
                                    <div class="form-group">
                                        <label class="form-label" for="verify-status">{{ __('Status') }} <span class="required">*</span></label>
                                        <select id="verify-status" name="status" class="form-select" required>
                                            @foreach ($verificationStatuses ?? \App\Support\Enums\DocumentVerificationStatus::cases() as $status)
                                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="verify-reason">{{ __('Rejection reason') }}</label>
                                        <input id="verify-reason" type="text" name="rejection_reason" class="form-control" placeholder="{{ __('Required when rejected') }}" />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="verify-notes">{{ __('Notes') }}</label>
                                        <textarea id="verify-notes" name="notes" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary">{{ __('Save verification') }}</button>
                                    </div>
                                </form>
                            </x-modal>
                        @endcan
                        @can('delete', $document)
                            <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" :title="__('Remove')"
                                @click="$store.confirm.ask({
                                    title: @js(__('Remove document?')),
                                    message: @js($document->categoryLabel().' '.__('will be deleted.')),
                                    action: @js(route('admissions.documents.destroy', $document)),
                                    method: 'DELETE'
                                })">
                                <x-icon name="trash" class="icon-sm" />
                            </button>
                        @endcan
                    </div>
                </div>
            @empty
                <x-empty-state icon="list" :title="__('No documents')" :message="__('Upload the applicant records for verification here.')" />
            @endforelse
        </x-card>

        {{-- Assessments --}}
        <x-card :title="__('Assessments')">
            @forelse ($application->assessments as $assessment)
                <div class="list-row">
                    <div style="min-width:0;">
                        <div style="font-weight: var(--weight-semibold);">
                            {{ $assessment->typeLabel() }}
                            <x-badge :color="$assessment->statusBadgeColor()">{{ $assessment->statusLabel() }}</x-badge>
                        </div>
                        <div class="text-xs text-muted">
                            {{ $assessment->scheduled_at?->format('M j, Y g:i A') }}
                            @if ($assessment->location)
                                · {{ $assessment->location }}
                            @endif
                        </div>
                        @if ($assessment->score !== null)
                            <div class="text-xs text-muted">{{ __('Score') }}: {{ $assessment->score }}/100</div>
                        @endif
                        @if ($assessment->notes)
                            <div class="text-xs text-muted">{{ $assessment->notes }}</div>
                        @endif
                    </div>
                    @if ($assessment->isOpen() && auth()->user()?->can('recordAssessment', $application))
                        <x-modal title="{{ __('Record assessment') }}">
                            <x-slot:trigger>
                                <button type="button" class="btn btn-ghost btn-sm">
                                    <x-icon name="pencil" class="icon-sm" />
                                </button>
                            </x-slot:trigger>
                            <form method="POST" action="{{ route('admissions.assessments.update', $assessment) }}">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label class="form-label" for="assessment-status">{{ __('Status') }} <span class="required">*</span></label>
                                    <select id="assessment-status" name="status" class="form-select" required>
                                        @foreach ($assessmentStatuses ?? \App\Support\Enums\AdmissionAssessmentStatus::cases() as $status)
                                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="assessment-score">{{ __('Score (0–100)') }}</label>
                                    <input id="assessment-score" type="number" name="score" class="form-control" min="0" max="100" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="assessment-notes">{{ __('Notes') }}</label>
                                    <textarea id="assessment-notes" name="notes" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                </div>
                            </form>
                        </x-modal>
                    @endif
                </div>
            @empty
                <x-empty-state icon="clipboard-check" :title="__('No assessments')" :message="__('No admission assessments have been scheduled yet.')" />
            @endforelse
        </x-card>

        {{-- Communications --}}
        <x-card style="grid-column: 1 / -1;">
            <x-slot:title>{{ __('Communication log') }}</x-slot:title>
            <x-slot:actions>
                @can('view', $application)
                    <x-modal title="{{ __('Log communication') }}">
                        <x-slot:trigger>
                            <button type="button" class="btn btn-ghost btn-sm">
                                <x-icon name="plus" class="icon-sm" />
                                {{ __('Log') }}
                            </button>
                        </x-slot:trigger>
                        <form method="POST" action="{{ route('admissions.communications.store', $application) }}">
                            @csrf
                            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-2);">
                                <div class="form-group">
                                    <label class="form-label" for="comm-type">{{ __('Type') }} <span class="required">*</span></label>
                                    <select id="comm-type" name="type" class="form-select" required>
                                        @foreach ($communicationTypes ?? \App\Support\Enums\CommunicationType::cases() as $type)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="comm-direction">{{ __('Direction') }} <span class="required">*</span></label>
                                    <select id="comm-direction" name="direction" class="form-select" required>
                                        <option value="inbound">{{ __('Inbound') }}</option>
                                        <option value="outbound">{{ __('Outbound') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-2);">
                                <div class="form-group">
                                    <label class="form-label" for="comm-contact">{{ __('Contact') }}</label>
                                    <input id="comm-contact" type="text" name="contact" class="form-control" placeholder="{{ __('Phone or email used') }}" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="comm-when">{{ __('Occurred at') }}</label>
                                    <input id="comm-when" type="datetime-local" name="occurred_at" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="comm-subject">{{ __('Subject') }}</label>
                                <input id="comm-subject" type="text" name="subject" class="form-control" />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="comm-message">{{ __('Message') }}</label>
                                <textarea id="comm-message" name="message" class="form-control" rows="3">{{ $application->primaryParent?->full_name ? __('Prepared for: :name', ['name' => $application->primaryParent->full_name]) : '' }}</textarea>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">{{ __('Log communication') }}</button>
                            </div>
                        </form>
                    </x-modal>
                @endcan
            </x-slot:actions>

            @forelse ($application->communications as $communication)
                <div class="list-row">
                    <div style="min-width:0;">
                        <div class="flex gap-1" style="align-items:center;">
                            <x-badge color="{{ $communication->direction === 'inbound' ? 'info' : 'primary' }}">
                                {{ $communication->directionLabel() }}
                            </x-badge>
                            <span style="font-weight: var(--weight-semibold);">{{ $communication->typeLabel() }}</span>
                            @if ($communication->subject)
                                <span class="text-sm">· {{ $communication->subject }}</span>
                            @endif
                        </div>
                        @if ($communication->message)
                            <div class="text-sm text-muted" style="margin-top: 2px;">{{ $communication->message }}</div>
                        @endif
                        <div class="text-xs text-muted" style="margin-top: 2px;">
                            {{ $communication->occurred_at?->format('M j, Y g:i A') }}
                            @if ($communication->contact)
                                · {{ $communication->contact }}
                            @endif
                            @if ($communication->creator)
                                · {{ $communication->creator->full_name }}
                            @endif
                        </div>
                    </div>
                    @can('update', $application)
                        <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" :title="__('Remove')"
                            @click="$store.confirm.ask({
                                title: @js(__('Remove communication?')),
                                message: @js($communication->typeLabel().' '.__('entry will be deleted.')),
                                action: @js(route('admissions.communications.destroy', [$application, $communication])),
                                method: 'DELETE'
                            })">
                            <x-icon name="trash" class="icon-sm" />
                        </button>
                    @endcan
                </div>
            @empty
                <x-empty-state icon="send" :title="__('No communication logged')" :message="__('Keep a record of every outreach and callback here.')" />
            @endforelse
        </x-card>
    </div>
</x-layouts.app>
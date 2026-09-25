<x-layouts.app :title="$student->full_name">
    <x-breadcrumb :items="[
        ['label' => 'Students', 'url' => route('students.index')],
        ['label' => $student->full_name],
    ]" />

    <div class="flex flex-col" style="gap: var(--space-3);">

        @php($currentEnrollment = collect($student->enrollments)->firstWhere('status', 'active'))

        <div class="profile-header">
            @if ($student->photo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($student->photo_path) }}" alt="{{ $student->full_name }}" class="profile-photo" />
            @else
                <x-avatar :initials="$student->initials()" size="lg" />
            @endif
            <div class="profile-meta">
                <h1 class="profile-name">{{ $student->full_name }}</h1>
                <div class="flex gap-1 flex-wrap" style="align-items:center;">
                    <span class="code-chip">{{ $student->student_number }}</span>
                    <x-badge :color="$student->statusBadgeColor()" :dot="true">{{ $student->statusLabel() }}</x-badge>
                    @if ($student->gradeLevel)
                        <x-badge color="primary">{{ $student->gradeLevel->name }}</x-badge>
                    @endif
                    @if ($student->classRoom)
                        <x-badge color="info">{{ $student->classRoom->name }}</x-badge>
                    @endif
                    @if ($student->academicYear)
                        <x-badge color="neutral">{{ $student->academicYear->name }}</x-badge>
                    @endif
                </div>
                @if ($student->national_id)
                    <div class="text-sm text-muted">National ID: <strong>{{ $student->national_id }}</strong></div>
                @endif
            </div>
            <div class="profile-actions">
                @can('view', $student)
                    <a href="{{ route('students.id-card', $student) }}" class="btn btn-secondary">
                        <x-icon name="credit-card" class="icon-sm" />
                        ID Card
                    </a>
                @endcan
                @can('transfer', $student)
                    <a href="{{ route('students.transfer', $student) }}" class="btn btn-secondary">
                        <x-icon name="refresh" class="icon-sm" />
                        Transfer
                    </a>
                @endcan
                @can('update', $student)
                    <a href="{{ route('students.edit', $student) }}" class="btn btn-secondary">
                        <x-icon name="pencil" class="icon-sm" />
                        Edit
                    </a>
                @endcan
                @can('delete', $student)
                    <button type="button" class="btn btn-danger-ghost"
                        @click="$store.confirm.ask({
                            title: 'Archive student?',
                            message: `Archive ${@js($student->full_name)} (${@js($student->student_number)}). Records are never permanently deleted.`,
                            action: @js(route('students.destroy', $student)),
                            method: 'DELETE',
                            confirmText: 'Archive'
                        })">
                        <x-icon name="archive" class="icon-sm" />
                        Archive
                    </button>
                @endcan
            </div>
        </div>

        {{-- Summary strip --}}
        <x-card>
            <div class="stat-grid" style="grid-template-columns: repeat(4, 1fr);">
                <div class="stat-cell">
                    <div class="text-xs text-muted">{{ __('Attendance') }}</div>
                    <div class="stat-value">{{ $attendancePercentage !== null ? $attendancePercentage.'%' : '—' }}</div>
                    <div class="text-xs text-muted">{{ $attendancePercentage !== null ? __('of sessions this year') : __('No attendance yet') }}</div>
                </div>
                <div class="stat-cell">
                    <div class="text-xs text-muted">{{ __('Section & Roll') }}</div>
                    <div class="stat-value">
                        {{ optional($student->classRoom)->name ?? '—' }}
                        <span class="text-muted" style="font-size:var(--text-sm);">{{ $currentEnrollment?->roll_number ? '#'.$currentEnrollment->roll_number : '' }}</span>
                    </div>
                    <div class="text-xs text-muted">
                        @if ($currentEnrollment)
                            {{ $currentEnrollment->academicYear?->name }} · {{ strtoupper($currentEnrollment->status) }}
                        @else
                            {{ __('Not enrolled this year') }}
                        @endif
                    </div>
                </div>
                <div class="stat-cell">
                    <div class="text-xs text-muted">{{ __('Parent') }}</div>
                    <div class="stat-value" style="font-size:var(--text-md);">
                        {{ $student->primaryParent?->full_name ?? '—' }}
                    </div>
                    <div class="text-xs text-muted">{{ $student->primaryGuardian?->phone ?? $student->primaryGuardian?->email ?? __('No contact') }}</div>
                </div>
                <div class="stat-cell">
                    <div class="text-xs text-muted">{{ __('Documents') }}</div>
                    <div class="stat-value">{{ number_format($student->documents->count()) }}</div>
                    <div class="text-xs text-muted">{{ __('Files on record') }}</div>
                </div>
            </div>
        </x-card>

        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-3); align-items:start;">

            <x-card title="Identity information">
                <div class="kv-list">
                    <div class="kv"><span>First name</span><strong>{{ $student->first_name }}</strong></div>
                    @if ($student->other_names)
                        <div class="kv"><span>Middle name</span><strong>{{ $student->other_names }}</strong></div>
                    @endif
                    <div class="kv"><span>Last name</span><strong>{{ $student->last_name }}</strong></div>
                    <div class="kv"><span>Gender</span><strong>{{ \Illuminate\Support\Str::headline($student->gender) }}</strong></div>
                    <div class="kv"><span>Date of birth</span><strong>{{ $student->date_of_birth?->format('M j, Y') }} · {{ $student->age() }} yrs</strong></div>
                    @if ($student->place_of_birth)
                        <div class="kv"><span>Place of birth</span><strong>{{ $student->place_of_birth }}</strong></div>
                    @endif
                    <div class="kv"><span>National ID</span><strong>{{ $student->national_id ?? '—' }}</strong></div>
                    <div class="kv"><span>Enrollment date</span><strong>{{ $student->enrollment_date?->format('M j, Y') }}</strong></div>
                    @if ($student->previous_school)
                        <div class="kv"><span>Previous school</span><strong>{{ $student->previous_school }}</strong></div>
                    @endif
                    @if ($student->address)
                        <div class="kv"><span>Address</span><strong>{{ $student->address }}</strong></div>
                    @endif
                </div>
            </x-card>

            <x-card title="Parents">
                @forelse ($student->parents as $guardian)
                    <a href="{{ '#' }}" class="parent-row" style="text-decoration:none;">
                        <x-avatar :initials="strtoupper(substr($guardian->first_name, 0, 1).substr($guardian->last_name, 0, 1))" size="sm" />
                        <div style="min-width:0;">
                            <div class="flex gap-1" style="align-items:center;">
                                <strong>{{ $guardian->full_name }}</strong>
                                @if ($guardian->pivot->is_primary)
                                    <x-badge color="primary" size="sm">primary</x-badge>
                                @endif
                            </div>
                            <div class="text-xs text-muted">{{ $guardian->relationshipLabel() }} · {{ $guardian->phone ?? $guardian->email ?? '—' }}</div>
                        </div>
                    </a>
                @empty
                    <p class="text-muted text-sm">No parent linked yet.</p>
                @endforelse
            </x-card>

        </div>

        <x-card title="Enrollment history">
            @forelse ($student->enrollments as $enrollment)
                <div class="enrollment-row">
                    <div class="enrollment-dot" style="background:{{ $enrollment->status === 'active' ? 'var(--color-success)' : 'var(--color-muted)' }};"></div>
                    <div style="flex:1;">
                        <div class="flex gap-1" style="align-items:center;">
                            <strong>{{ $enrollment->academicYear?->name ?? '—' }}</strong>
                            <x-badge :color="$enrollment->status === 'active' ? 'success' : 'neutral'" size="sm">{{ ucfirst($enrollment->status) }}</x-badge>
                            @if ($enrollment->result && $enrollment->result !== $enrollment->status)
                                <x-badge color="info" size="sm">{{ ucfirst($enrollment->result) }}</x-badge>
                            @endif
                        </div>
                        <div class="text-xs text-muted">
                            {{ $enrollment->gradeLevel?->name ?? '—' }}{{ $enrollment->classRoom ? ' · '.$enrollment->classRoom->name : '' }}
                            @if ($enrollment->roll_number)
                                · Roll #{{ $enrollment->roll_number }}
                            @endif
                            · {{ $enrollment->enrolled_at?->format('M Y') }}{{ $enrollment->left_at ? ' — '.$enrollment->left_at->format('M Y') : '' }}
                        </div>
                    </div>
                </div>
            @empty
                <x-empty-state icon="calendar" title="No enrollments yet" message="Enrollment history will appear here as the student progresses through the school." />
            @endforelse
        </x-card>

        {{-- Medical record --}}
        @can('viewMedical', $student)
            <x-card id="medical" title="Medical record">
                @php($medical = $student->medicalRecord)
                <div x-data="{ open: false }">
                    @if ($medical && ($medical->blood_group || $medical->allergies || $medical->medical_conditions || $medical->medications || $medical->disability_support || $medical->doctor_name || $medical->emergency_hospital || $medical->health_notes))
                        <div class="kv-list" style="grid-template-columns: 1fr 1fr;">
                            @if ($medical->blood_group)
                                <div class="kv"><span>Blood group</span><strong>{{ $medical->blood_group }}</strong></div>
                            @endif
                            @if ($medical->allergies)
                                <div class="kv"><span>Allergies</span><strong>{{ $medical->allergies }}</strong></div>
                            @endif
                            @if ($medical->medical_conditions)
                                <div class="kv"><span>Medical conditions</span><strong>{{ $medical->medical_conditions }}</strong></div>
                            @endif
                            @if ($medical->medications)
                                <div class="kv"><span>Medications</span><strong>{{ $medical->medications }}</strong></div>
                            @endif
                            @if ($medical->disability_support)
                                <div class="kv"><span>Disability support</span><strong>{{ $medical->disability_support }}</strong></div>
                            @endif
                            @if ($medical->doctor_name || $medical->emergency_hospital)
                                <div class="kv"><span>Doctor / hospital</span><strong>{{ $medical->doctor_name }}{{ $medical->doctor_name && $medical->emergency_hospital ? ' · ' : '' }}{{ $medical->emergency_hospital }}</strong></div>
                            @endif
                            @if ($medical->health_notes)
                                <div class="kv" style="grid-column:1 / -1;"><span>Health notes</span><strong>{{ $medical->health_notes }}</strong></div>
                            @endif
                        </div>
                    @else
                        <p class="text-muted text-sm">No medical record on file.</p>
                    @endif

                    <button type="button" class="btn btn-secondary btn-sm mt-2" @click="open = !open">
                        <x-icon name="heart" class="icon-sm" />
                        {{ __('Edit medical record') }}
                    </button>

                    <form method="POST" action="{{ route('students.medical.update', $student) }}" class="mt-2" x-show="open" x-cloak style="display:none;">
                        @csrf
                        @method('PUT')
                        <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                            <x-input name="blood_group" label="Blood group" :value="old('blood_group', $medical?->blood_group)" placeholder="e.g. O+" />
                            <x-input name="doctor_name" label="Doctor name" :value="old('doctor_name', $medical?->doctor_name)" />
                            <x-input name="emergency_hospital" label="Emergency hospital" :value="old('emergency_hospital', $medical?->emergency_hospital)" />
                            <x-input name="allergies" label="Allergies" :value="old('allergies', $medical?->allergies)" />
                            <x-input name="medical_conditions" label="Medical conditions" :value="old('medical_conditions', $medical?->medical_conditions)" />
                            <x-input name="medications" label="Medications" :value="old('medications', $medical?->medications)" />
                            <x-input name="disability_support" label="Disability support" :value="old('disability_support', $medical?->disability_support)" />
                            <x-input name="health_notes" label="Health notes" :value="old('health_notes', $medical?->health_notes)" />
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">
                            <x-icon name="check" class="icon-sm" />
                            {{ __('Save medical record') }}
                        </button>
                    </form>
                </div>
            </x-card>
        @endcan

        {{-- Emergency contacts --}}
        <x-card id="emergency" title="Emergency contacts">
            <div x-data="{ open: false }">
                <div class="flex flex-col" style="gap: var(--space-2);">
                    @forelse ($student->emergencyContacts as $contact)
                        <div class="parent-row">
                            <x-avatar :initials="strtoupper(substr($contact->name, 0, 2))" size="sm" />
                            <div style="min-width:0; flex:1;">
                                <div class="flex gap-1" style="align-items:center;">
                                    <strong>{{ $contact->name }}</strong>
                                    <x-badge color="info" size="sm">{{ $contact->relationship }}</x-badge>
                                    @if ($contact->authorized_pickup)
                                        <x-badge color="success" size="sm"><x-icon name="check" class="icon-sm" />{{ __('Pickup') }}</x-badge>
                                    @endif
                                </div>
                                <div class="text-xs text-muted">
                                    Priority {{ $contact->priority }} · {{ $contact->phone }}
                                    @if ($contact->notes)
                                        · {{ $contact->notes }}
                                    @endif
                                </div>
                            </div>
                            @can('update', $student)
                                <form method="POST" action="{{ route('students.emergency-contacts.destroy', [$student, $contact]) }}" onsubmit="return confirm('Remove this emergency contact?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm btn-icon text-danger" :title="__('Remove')">
                                        <x-icon name="trash" class="icon-sm" />
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <p class="text-muted text-sm">No emergency contacts on file.</p>
                    @endforelse

                    @can('update', $student)
                        <button type="button" class="btn btn-secondary btn-sm" @click="open = !open" style="align-self:flex-start;">
                            <x-icon name="phone" class="icon-sm" />
                            {{ __('Add emergency contact') }}
                        </button>

                        <form method="POST" action="{{ route('students.emergency-contacts.store', $student) }}" x-show="open" x-cloak style="display:none;">
                            @csrf
                            <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                                <x-input name="name" label="Full name" :value="old('name')" required />
                                <x-input name="relationship" label="Relationship" :value="old('relationship')" placeholder="e.g. aunt, neighbour" required />
                                <x-input name="phone" label="Phone" :value="old('phone')" required />
                                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-2);">
                                    <x-input name="priority" label="Priority" type="number" :value="old('priority', 1)" />
                                    <div class="form-group" style="align-self:end;">
                                        <label class="form-label" for="authorized_pickup">
                                            <input type="checkbox" id="authorized_pickup" name="authorized_pickup" value="1" @checked(old('authorized_pickup')) />
                                            Authorized pickup
                                        </label>
                                    </div>
                                </div>
                                <x-input name="notes" label="Notes" :value="old('notes')" />
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mt-2">
                                <x-icon name="plus" class="icon-sm" />
                                {{ __('Add contact') }}
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </x-card>

        {{-- Documents --}}
        @can('viewDocuments', $student)
            <x-card id="documents" title="Documents">
                <div x-data="{ uploadOpen: false }">
                    <div class="flex flex-col" style="gap: var(--space-2);">
                        @forelse ($student->documents as $document)
                            <div class="guardian-row">
                                <x-avatar :initials="strtoupper(substr($document->category, 0, 2))" size="sm" />
                                <div style="min-width:0; flex:1;">
                                    <div class="flex gap-1" style="align-items:center;">
                                        <strong>{{ $document->name }}</strong>
                                        <x-badge :color="$document->verified ? 'success' : 'neutral'" size="sm">
                                            {{ $document->verified ? __('Verified') : __('Pending') }}
                                        </x-badge>
                                        <span class="text-xs text-muted">{{ $document->categoryLabel() }}</span>
                                    </div>
                                    <div class="text-xs text-muted">
                                        {{ $document->uploadedBy?->full_name ?? '—' }} · {{ $document->created_at?->format('M j, Y') }}
                                        @if ($document->verified_at)
                                            · verified by {{ $document->verifiedBy?->full_name ?? '—' }}
                                        @endif
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($document->path) }}" target="_blank" class="btn btn-ghost btn-sm btn-icon" :title="__('Download')">
                                        <x-icon name="download" class="icon-sm" />
                                    </a>
                                    <form method="POST" action="{{ route('students.documents.verify', [$student, $document]) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-ghost btn-sm btn-icon" :title="__('Toggle verification')">
                                            <x-icon name="shield-check" class="icon-sm" />
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('students.documents.destroy', [$student, $document]) }}" onsubmit="return confirm('Delete this document?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm btn-icon text-danger" :title="__('Delete')">
                                            <x-icon name="trash" class="icon-sm" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-sm">No documents on file.</p>
                        @endforelse

                        <button type="button" class="btn btn-secondary btn-sm" @click="uploadOpen = !uploadOpen" style="align-self:flex-start;">
                            <x-icon name="upload" class="icon-sm" />
                            {{ __('Upload document') }}
                        </button>

                        <form method="POST" action="{{ route('students.documents.store', $student) }}" enctype="multipart/form-data" x-show="uploadOpen" x-cloak style="display:none;">
                            @csrf
                            <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                                <div class="form-group">
                                    <label class="form-label" for="category">Category</label>
                                    <select id="category" name="category" class="form-select" required>
                                        @foreach ($documentCategories as $category)
                                            <option value="{{ $category->value }}" @selected(old('category') === $category->value)>{{ $category->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
                                    @enderror
                                </div>
                                <x-input name="name" label="Document name (optional)" :value="old('name')" />
                                <div class="form-group">
                                    <label class="form-label" for="file">File</label>
                                    <input id="file" name="file" type="file" class="form-select" required />
                                    @error('file')
                                        <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
                                    @enderror
                                </div>
                                <x-input name="notes" label="Notes" :value="old('notes')" />
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mt-2">
                                <x-icon name="upload" class="icon-sm" />
                                {{ __('Upload') }}
                            </button>
                        </form>
                    </div>
                </div>
            </x-card>
        @endcan

        {{-- Transfers --}}
        <x-card title="Transfer history">
            @forelse ($student->transfers as $transfer)
                <div class="enrollment-row">
                    <div class="enrollment-dot" style="background:{{ $transfer->type === 'internal' ? 'var(--color-info)' : 'var(--color-warning)' }};"></div>
                    <div style="flex:1;">
                        <div class="flex gap-1" style="align-items:center;">
                            <strong>{{ $transfer->typeLabel() }}</strong>
                            <x-badge color="info" size="sm">{{ $transfer->transfer_date?->format('M j, Y') }}</x-badge>
                        </div>
                        <div class="text-xs text-muted">
                            @if ($transfer->type === 'internal')
                                {{ $transfer->fromClassRoom?->name ?? '—' }} → {{ $transfer->toClassRoom?->name ?? '—' }}
                            @else
                                {{ __('To') }} {{ $transfer->destination_school ?? 'another school' }}
                                @if ($transfer->certificate_number)
                                    · {{ __('Certificate') }} {{ $transfer->certificate_number }}
                                @endif
                            @endif
                            @if ($transfer->reason)
                                · {{ $transfer->reason }}
                            @endif
                            @if ($transfer->approvedBy)
                                · {{ __('Approved by') }} {{ $transfer->approvedBy->full_name }}
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted text-sm">No transfers recorded.</p>
            @endforelse
        </x-card>

        {{-- Timeline --}}
        <x-card title="Timeline">
            @forelse ($student->timeline as $event)
                <div class="enrollment-row">
                    <div class="enrollment-dot" style="background:var(--color-muted);"></div>
                    <div style="flex:1;">
                        <div class="text-sm">{{ $event->description }}</div>
                        <div class="text-xs text-muted">
                            {{ $event->event_date?->format('M j, Y') }}
                            @if ($event->createdBy)
                                · {{ $event->createdBy->full_name }}
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted text-sm">No activity recorded yet.</p>
            @endforelse
        </x-card>

    </div>
</x-layouts.app>
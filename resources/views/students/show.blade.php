<x-layouts.app :title="$student->full_name">
    <x-breadcrumb :items="[
        ['label' => 'Students', 'url' => route('students.index')],
        ['label' => $student->full_name],
    ]" />

    <div class="flex flex-col" style="gap: var(--space-3);">

        <div class="profile-header">
            <x-avatar :initials="$student->initials()" size="lg" />
            <div class="profile-meta">
                <h1 class="profile-name">{{ $student->full_name }}</h1>
                <div class="flex gap-1 flex-wrap">
                    <span class="code-chip">{{ $student->student_number }}</span>
                    <x-badge :color="$student->statusBadgeColor()" :dot="true">{{ $student->statusLabel() }}</x-badge>
                    @if ($student->gradeLevel)
                        <x-badge color="primary">{{ $student->gradeLevel->name }}</x-badge>
                    @endif
                    @if ($student->classRoom)
                        <x-badge color="info">{{ $student->classRoom->name }}</x-badge>
                    @endif
                </div>
            </div>
            <div class="profile-actions">
                @can('view', $student)
                    <a href="{{ route('students.id-card', $student) }}" class="btn btn-secondary">
                        <x-icon name="credit-card" class="icon-sm" />
                        ID Card
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
                            message: `Archive ${@js($student->full_name)} (${@js($student->student_number)}). This cannot be undone.`,
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

        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-3); align-items:start;">

            <x-card title="Personal information">
                <div class="kv-list">
                    <div class="kv"><span>First name</span><strong>{{ $student->first_name }}</strong></div>
                    @if ($student->other_names)
                        <div class="kv"><span>Other names</span><strong>{{ $student->other_names }}</strong></div>
                    @endif
                    <div class="kv"><span>Last name</span><strong>{{ $student->last_name }}</strong></div>
                    <div class="kv"><span>Gender</span><strong>{{ \Illuminate\Support\Str::headline($student->gender) }}</strong></div>
                    <div class="kv"><span>Date of birth</span><strong>{{ $student->date_of_birth?->format('M j, Y') }} · {{ $student->age() }} yrs</strong></div>
                    <div class="kv"><span>National ID</span><strong>{{ $student->national_id ?? '—' }}</strong></div>
                    <div class="kv"><span>Enrollment date</span><strong>{{ $student->enrollment_date?->format('M j, Y') }}</strong></div>
                    @if ($student->previous_school)
                        <div class="kv"><span>Previous school</span><strong>{{ $student->previous_school }}</strong></div>
                    @endif
                </div>
            </x-card>

            <x-card title="Guardians">
                @forelse ($student->guardians as $guardian)
                    <div class="guardian-row">
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
                    </div>
                @empty
                    <p class="text-muted text-sm">No guardian linked yet.</p>
                @endforelse

                @if ($student->address || $student->health_notes)
                    <div style="border-top: 1px solid var(--color-border); margin-top: var(--space-3); padding-top: var(--space-3);">
                        @if ($student->address)
                            <div class="kv"><span>Address</span><strong>{{ $student->address }}</strong></div>
                        @endif
                        @if ($student->health_notes)
                            <div class="kv"><span>Health notes</span><strong>{{ $student->health_notes }}</strong></div>
                        @endif
                    </div>
                @endif
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
                        </div>
                        <div class="text-xs text-muted">
                            {{ $enrollment->gradeLevel?->name ?? '—' }}{{ $enrollment->classRoom ? ' · '.$enrollment->classRoom->name : '' }}
                            · {{ $enrollment->enrolled_at?->format('M Y') }}{{ $enrollment->left_at ? ' — '.$enrollment->left_at->format('M Y') : '' }}
                        </div>
                    </div>
                </div>
            @empty
                <x-empty-state icon="calendar" title="No enrollments yet" message="Enrollment history will appear here as the student progresses through the school." />
            @endforelse
        </x-card>

    </div>
</x-layouts.app>
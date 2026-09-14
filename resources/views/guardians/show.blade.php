<x-layouts.app :title="$guardian->full_name">
    <x-breadcrumb :items="[
        ['label' => 'Guardians', 'url' => route('guardians.index')],
        ['label' => $guardian->full_name],
    ]" />

    <x-page-header :title="$guardian->full_name"
        :description="$guardian->relationshipLabel().' · '.($guardian->occupation ?? 'Guardian')">
        <span style="display:inline-flex; align-items:center; gap: var(--space-2); flex-wrap:wrap;">
            @can('update', $guardian)
                <a href="{{ route('guardians.edit', $guardian) }}" class="btn btn-secondary">
                    <x-icon name="pencil" class="icon-sm" />
                    Edit
                </a>
            @endcan
            <a href="{{ route('guardians.index') }}" class="btn btn-secondary">
                <x-icon name="chevron-left" class="icon-sm" />
                All guardians
            </a>
        </span>
    </x-page-header>

    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-4); align-items:start;">

        <x-card title="Contact details">
            <dl class="detail-list">
                <div><dt>Full name</dt><dd>{{ $guardian->full_name }}</dd></div>
                <div><dt>Relationship</dt><dd>{{ $guardian->relationshipLabel() }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $guardian->phone ?? '—' }}</dd></div>
                <div><dt>Email</dt><dd>{{ $guardian->email ?? '—' }}</dd></div>
                <div><dt>Occupation</dt><dd>{{ $guardian->occupation ?? '—' }}</dd></div>
                <div><dt>National ID</dt><dd>{{ $guardian->national_id ?? '—' }}</dd></div>
                <div><dt>Address</dt><dd>{{ $guardian->address ?? '—' }}</dd></div>
                <div><dt>Linked account</dt><dd>{{ $guardian->user?->full_name ?? '—' }}</dd></div>
            </dl>
        </x-card>

        <x-card title="Linked students" :subtitle="$guardian->students_count . ' student(s)'">
            @forelse ($guardian->students as $student)
                <div class="feed-item" style="padding: var(--space-2) 0;">
                    <x-avatar :initials="$student->initials()" size="sm" />
                    <div style="min-width:0;">
                        <a href="{{ route('students.show', $student) }}" class="text-sm" style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">
                            {{ $student->full_name }}
                        </a>
                        <div class="text-xs text-muted">
                            {{ $student->student_number }} · {{ $student->gradeLevel->name ?? '—' }} {{ $student->classRoom->name ?? '' }}
                            @if ($student->guardian_id === $guardian->getKey())
                                · <span class="chip chip-info" style="font-size:11px;">Primary</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <x-empty-state icon="users" title="No students linked" message="This guardian is not linked to any student yet." />
            @endforelse
        </x-card>

    </div>

</x-layouts.app>
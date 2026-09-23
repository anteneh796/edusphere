<x-layouts.app :title="__('Transfer Student')">
    <x-breadcrumb :items="[
        ['label' => __('Students'), 'url' => route('students.index')],
        ['label' => $student->full_name, 'url' => route('students.show', $student)],
        ['label' => __('Transfer')],
    ]" />

    <div class="flex flex-col" style="gap: var(--space-3);">

        <div class="profile-header">
            <x-avatar :initials="$student->initials()" size="lg" />
            <div class="profile-meta">
                <h1 class="profile-name">{{ $student->full_name }}</h1>
                <div class="flex gap-1 flex-wrap">
                    <span class="code-chip">{{ $student->student_number }}</span>
                    @if ($student->gradeLevel)
                        <x-badge color="primary">{{ $student->gradeLevel->name }}</x-badge>
                    @endif
                    @if ($student->classRoom)
                        <x-badge color="info">{{ $student->classRoom->name }}</x-badge>
                    @endif
                    <x-badge :color="$student->statusBadgeColor()">{{ $student->statusLabel() }}</x-badge>
                </div>
            </div>
        </div>

        <div x-data="{ type: 'internal' }">

            <x-card title="Transfer type">
                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-2);">
                    <label class="form-label" style="align-items:center; gap: var(--space-2); cursor:pointer;">
                        <input type="radio" name="type" value="internal" x-model="type" />
                        <strong>Internal transfer</strong>
                        <span class="text-xs text-muted">— change section, same school</span>
                    </label>
                    <label class="form-label" style="align-items:center; gap: var(--space-2); cursor:pointer;">
                        <input type="radio" name="type" value="external" x-model="type" />
                        <strong>External transfer</strong>
                        <span class="text-xs text-muted">— student leaves the school</span>
                    </label>
                </div>
            </x-card>

            <form method="POST" action="{{ route('students.transfer.store', $student) }}" novalidate>
                @csrf
                <input type="hidden" name="type" :value="type" />

                <x-card title="Internal transfer" x-show="type === 'internal'" x-cloak>
                    <div class="form-group">
                        <label class="form-label" for="to_class_room_id">Destination section <span class="required">*</span></label>
                        <select id="to_class_room_id" name="to_class_room_id" class="form-select">
                            <option value="">Select section…</option>
                            @foreach ($classRooms as $class)
                                @if (! $student->classRoom || $class->id !== $student->classRoom->id)
                                    <option value="{{ $class->id }}" @selected(old('to_class_room_id') === $class->id)>
                                        {{ $class->gradeLevel->name }} · {{ $class->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('to_class_room_id')
                            <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
                        @enderror
                        <div class="form-hint">The student keeps the same grade and stays in the {{ \App\Domains\Academics\Models\AcademicYear::current()->first()?->name ?? 'current' }} academic year.</div>
                    </div>
                </x-card>

                <x-card title="External transfer" x-show="type === 'external'" x-cloak>
                    <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                        <x-input name="destination_school" label="Destination school" :value="old('destination_school')" placeholder="Name of the new school" />
                        <x-input name="transfer_date" type="date" label="Transfer date" :value="old('transfer_date', date('Y-m-d'))" />
                        <x-input name="certificate_number" label="Transfer certificate no." :value="old('certificate_number')" placeholder="Optional" />
                        <x-input name="reason" label="Reason" :value="old('reason')" placeholder="e.g. family relocation" />
                        <x-input name="notes" label="Notes" :value="old('notes')" />
                    </div>
                    <div class="form-hint mt-2">Once transferred the student record is preserved with status "Transferred". It is never deleted.</div>
                </x-card>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="refresh" class="icon-sm" />
                        {{ __('Complete transfer') }}
                    </button>
                    <a href="{{ route('students.show', $student) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>

    </div>
</x-layouts.app>
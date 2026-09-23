<x-layouts.app :title="__('Curriculum')">
    <x-page-header :title="__('Curriculum')" :description="__('Manage the units and pacing for your classes.')" />

    <x-card :title="__('Add a curriculum unit')">
        <form method="POST" action="{{ route('cms.teacher.curriculum.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf

            <x-select
                name="class_subject_id"
                :label="__('Class subject')"
                :options="$subjects->mapWithKeys(fn ($s) => [$s->getKey() => ($s->subject?->name ?? '—') . ' — ' . ($s->classRoom?->name ?? '')])"
                placeholder="{{ __('Select subject…') }}"
                required />

            <x-input
                name="title"
                :label="__('Unit title')"
                :value="old('title')"
                required />

            <x-textarea
                name="description"
                :label="__('Description')"
                :value="old('description')" />

            <x-input
                name="position"
                type="number"
                :label="__('Position')"
                :value="old('position')" />

            <x-input
                name="total_lessons"
                type="number"
                :label="__('Total lessons')"
                :value="old('total_lessons', '1')" />

            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary">{{ __('Add unit') }}</button>
            </div>
        </form>
    </x-card>

    <x-card :title="__('Curriculum units')">
        @forelse ($curriculumUnits as $unit)
            @php($color = match ($unit->status) {
                'completed' => 'success',
                'in_progress' => 'warning',
                default => 'neutral',
            })
            <details class="py-3 border-b border-border last:border-0">
                <summary class="flex items-center justify-between gap-4 cursor-pointer list-none">
                    <div>
                        <p class="font-medium">{{ $unit->title }}</p>
                        <p class="text-sm text-foreground-muted">
                            {{ $unit->classSubject?->subject?->name ?? '—' }}
                            · {{ $unit->classSubject?->classRoom?->name ?? '—' }}
                        </p>
                        <p class="text-xs text-foreground-muted mt-1">
                            {{ __('Unit :position', ['position' => $unit->position]) }}
                            · {{ __(':covered / :total lessons', ['covered' => $unit->covered_lessons, 'total' => $unit->total_lessons]) }}
                        </p>
                    </div>
                    <span class="badge badge-{{ $color }}">{{ $unit->status ?? 'pending' }}</span>
                </summary>

                <form method="POST" action="{{ route('cms.teacher.curriculum.update', $unit) }}" class="grid gap-4 sm:grid-cols-2 mt-4" style="border-top: 1px solid var(--color-border); padding-top: var(--space-4);">
                    @csrf
                    @method('PUT')

                    <x-input
                        name="title"
                        :label="__('Unit title')"
                        :value="old('title', $unit->title)"
                        required />

                    <x-input
                        name="position"
                        type="number"
                        :label="__('Position')"
                        :value="old('position', $unit->position)" />

                    <x-input
                        name="total_lessons"
                        type="number"
                        :label="__('Total lessons')"
                        :value="old('total_lessons', $unit->total_lessons)" />

                    <x-input
                        name="covered_lessons"
                        type="number"
                        :label="__('Covered lessons')"
                        :value="old('covered_lessons', $unit->covered_lessons)" />

                    <x-select
                        name="status"
                        :label="__('Status')"
                        :options="[
                            'pending' => __('Pending'),
                            'in_progress' => __('In progress'),
                            'completed' => __('Completed'),
                        ]"
                        :value="old('status', $unit->status)"
                        required />

                    <x-input
                        name="started_at"
                        type="date"
                        :label="__('Started on')"
                        :value="old('started_at', $unit->started_at?->format('Y-m-d'))" />

                    <x-input
                        name="completed_at"
                        type="date"
                        :label="__('Completed on')"
                        :value="old('completed_at', $unit->completed_at?->format('Y-m-d'))" />

                    <div class="sm:col-span-2">
                        <button type="submit" class="btn btn-primary">{{ __('Update unit') }}</button>
                    </div>
                </form>
            </details>
        @empty
            <x-empty-state
                icon="layers"
                :title="__('No curriculum units yet')"
                :message="__('Add your first unit above to plan the term.')" />
        @endforelse
    </x-card>
</x-layouts.app>
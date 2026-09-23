<x-layouts.app :title="__('Homework')">
    <x-page-header
        :title="__('Homework')"
        :description="$ward ? __('Assignments published for :name', ['name' => $ward->full_name]) : __('Homework')">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    @if (! $ward)
        <x-card>
            <x-empty-state icon="users" :title="__('No child selected')" :message="__('Link or select a child to view homework.')" />
        </x-card>
    @else
        <div class="grid gap-4">
            @forelse ($assignments as $assignment)
                <x-card :title="$assignment->title" :subtitle="$assignment->classSubject?->subject?->name" hover>
                    <p class="text-sm">{{ $assignment->description }}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <span class="badge badge-info">{{ $assignment->due_on?->isPast() ? __('Overdue') : __('Due :date', ['date' => $assignment->due_on?->format('d M Y') ?? '—']) }}</span>
                        <span class="badge badge-neutral">{{ $assignment->unit ?? __('General') }}</span>
                        @if ($assignment->submissions->isNotEmpty())
                            <span class="badge badge-success">{{ __('Submitted') }}</span>
                        @endif
                    </div>
                </x-card>
            @empty
                <x-card>
                    <x-empty-state icon="pencil" :title="__('No homework')" :message="__('Published assignments will appear here.')" />
                </x-card>
            @endforelse
        </div>
    @endif
</x-layouts.app>
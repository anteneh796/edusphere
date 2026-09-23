<x-layouts.app :title="__('Ward snapshot')">
    @php
        $wardDescription = __("A focused look at this ward's attendance and results.");
    @endphp
    <x-page-header
        :title="$ward->full_name"
        :description="$wardDescription">
        <a href="{{ route('cms.parent.dashboard') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Back to dashboard') }}
        </a>
    </x-page-header>

    <div class="grid grid-stats">
        <x-stat-card :label="__('Student number')" :value="$ward->student_number" icon="hash" color="primary" />
        <x-stat-card :label="__('Class')" :value="$ward->classRoom?->name ?? '—'" icon="book-open" color="info" />
        <x-stat-card :label="__('Present (recent)')" :value="$recentAttendance->where('status', 'present')->count().' / '.$recentAttendance->count()" icon="clipboard-check" color="success" />
        <x-stat-card :label="__('Latest results')" :value="$latestResults->count()" icon="award" color="accent" />
    </div>

    <div class="grid grid-2 mt-4">
        <x-card :title="__('Recent attendance')">
            @forelse ($recentAttendance as $record)
                <div class="list-row">
                    <span class="badge {{ strtolower((string) $record->status) === 'present' ? 'badge-success' : 'badge-warning' }}">
                        {{ ucfirst((string) $record->status) }}
                    </span>
                    <div class="text-sm">
                        {{ $record->session?->classRoom?->name }}
                        <div class="text-xs text-light">{{ $record->session?->date?->format('M j, Y') }}</div>
                    </div>
                </div>
            @empty
                <x-empty-state icon="clipboard" :title="__('No attendance yet')" :message="__('Attendance will appear here once recorded for this ward.')" />
            @endforelse
        </x-card>

        <x-card :title="__('Latest results')">
            @forelse ($latestResults as $result)
                <div class="list-row">
                    <x-icon name="award" class="icon-sm text-light" />
                    <div class="text-sm">
                        {{ $result?->examSubject?->subject?->name ?? '—' }}
                        <div class="text-xs text-light">{{ $result?->examSubject?->exam?->name ?? __('Exam') }}</div>
                    </div>
                    <span class="text-sm-semibold">{{ $result?->marks_obtained ?? '—' }}</span>
                </div>
            @empty
                <x-empty-state icon="award" :title="__('No results yet')" :message="__('Published results for this ward will appear here.')" />
            @endforelse
        </x-card>
    </div>
</x-layouts.app>

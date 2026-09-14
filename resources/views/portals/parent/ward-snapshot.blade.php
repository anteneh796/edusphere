<x-layouts.app :title="'Ward snapshot'">
    <x-page-header
        :title="$ward->full_name"
        description="A focused look at this ward's attendance and results.">
        <a href="{{ route('portals.parent.dashboard') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            Back to dashboard
        </a>
    </x-page-header>

    <div class="grid grid-stats">
        <x-stat-card label="Student number" :value="$ward->student_number" icon="hash" color="primary" />
        <x-stat-card label="Class" :value="$ward->classRoom?->name ?? '—'" icon="book-open" color="info" />
        <x-stat-card label="Present (recent)" :value="$recentAttendance->where('status', 'present')->count().' / '.$recentAttendance->count()" icon="clipboard-check" color="success" />
        <x-stat-card label="Latest results" :value="$latestResults->count()" icon="award" color="accent" />
    </div>

    <div class="grid grid-2 mt-4">
        <x-card title="Recent attendance">
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
                <x-empty-state icon="clipboard" title="No attendance yet" message="Attendance will appear here once recorded for this ward." />
            @endforelse
        </x-card>

        <x-card title="Latest results">
            @forelse ($latestResults as $result)
                <div class="list-row">
                    <x-icon name="award" class="icon-sm text-light" />
                    <div class="text-sm">
                        {{ $result->subject?->name }}
                        <div class="text-xs text-light">{{ $result->exam?->name ?? 'Exam' }}</div>
                    </div>
                    <span class="text-sm-semibold">{{ $result->marks_obtained ?? $result->score_obtained ?? '—' }}</span>
                </div>
            @empty
                <x-empty-state icon="award" title="No results yet" message="Published results for this ward will appear here." />
            @endforelse
        </x-card>
    </div>
</x-layouts.app>

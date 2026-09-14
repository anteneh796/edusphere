<x-layouts.app :title="'My dashboard'">
    <x-page-header title="My dashboard" description="Your snapshot — attendance and recent results.">
        <a href="{{ route('portals.student.attendance') }}" class="btn btn-ghost">Attendance</a>
        <a href="{{ route('portals.student.results') }}" class="btn btn-ghost">My results</a>
    </x-page-header>

    @if (! $student)
        <x-card title="Welcome">
            <x-empty-state
                icon="graduation"
                title="Account not linked to a student"
                message="Ask the registrar to link your login to your student profile, then refresh." />
        </x-card>
    @else
        <div class="grid grid-stats">
            <x-stat-card label="Class" :value="$student->classRoom?->name ?? '—'" icon="book-open" />
            <x-stat-card label="Grade" :value="$student->gradeLevel?->name ?? '—'" icon="layers" />
            <x-stat-card label="Present today" :value="$attendance['present'] ?? 0" icon="clipboard-check" color="success" />
            <x-stat-card label="Student No." :value="$student->student_number" icon="hash" />
        </div>

        <div class="grid grid-2 mt-4">
            <x-card title="Attendance overview">
                @forelse ($attendance['records'] ?? collect() as $record)
                    <div class="list-row">
                        <span class="{{ $record->status === 'present' ? 'badge badge-success' : 'badge badge-warning' }}">
                            {{ ucfirst($record->status) }}
                        </span>
                        <div class="text-sm">{{ $record->date }}</div>
                    </div>
                @empty
                    <x-empty-state icon="clipboard" title="No attendance yet" message="Your attendance records will show here once recorded." />
                @endforelse
            </x-card>

            <x-card title="Recent results">
                @forelse ($results as $result)
                    <div class="list-row">
                        <div class="text-sm">{{ $result->subject }}</div>
                        <div class="text-sm-semibold">{{ $result->score }}</div>
                    </div>
                @empty
                    <x-empty-state icon="award" title="No results yet" message="Published results will appear here." />
                @endforelse
            </x-card>
        </div>
    @endif
</x-layouts.app>

<x-layouts.app :title="__('My dashboard')">
    <x-page-header :title="__('My dashboard')" :description="__('Your snapshot — attendance and recent results.')">
        <a href="{{ route('cms.student.attendance') }}" class="btn btn-ghost">{{ __('Attendance') }}</a>
        <a href="{{ route('cms.student.results') }}" class="btn btn-ghost">{{ __('My results') }}</a>
    </x-page-header>

    @if (! $student)
        <x-card :title="__('Welcome')">
            <x-empty-state
                icon="graduation"
                :title="__('Account not linked to a student')"
                :message="__('Ask the registrar to link your login to your student profile, then refresh.')" />
        </x-card>
    @else
        <div class="grid grid-stats">
            <x-stat-card :label="__('Class')" :value="$student->classRoom?->name ?? '—'" icon="book-open" />
            <x-stat-card :label="__('Grade')" :value="$student->gradeLevel?->name ?? '—'" icon="layers" />
            <x-stat-card :label="__('Present today')" :value="$attendance['present'] ?? 0" icon="clipboard-check" color="success" />
            <x-stat-card :label="__('Student No.')" :value="$student->student_number" icon="hash" />
        </div>

        <div class="grid grid-2 mt-4">
            <x-card :title="__('Attendance overview')">
                @forelse ($attendance['records'] ?? collect() as $record)
                    <div class="list-row">
                        <span class="{{ $record->status === 'present' ? 'badge badge-success' : 'badge badge-warning' }}">
                            {{ ucfirst($record->status) }}
                        </span>
                        <div class="text-sm">{{ $record?->session?->date?->format('M j, Y') ?? '—' }}</div>
                    </div>
                @empty
                    <x-empty-state icon="clipboard" :title="__('No attendance yet')" :message="__('Your attendance records will show here once recorded.')" />
                @endforelse
            </x-card>

            <x-card :title="__('Recent results')">
                @forelse ($results as $result)
                    <div class="list-row">
                        <div class="text-sm">{{ $result?->examSubject?->subject?->name ?? '—' }}</div>
                        <div class="text-sm-semibold">{{ $result?->marks_obtained ?? '—' }}</div>
                    </div>
                @empty
                    <x-empty-state icon="award" :title="__('No results yet')" :message="__('Published results will appear here.')" />
                @endforelse
            </x-card>
        </div>
    @endif
</x-layouts.app>

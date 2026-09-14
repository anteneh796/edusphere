<x-layouts.app :title="'My attendance'">
    <x-page-header
        title="My attendance"
        description="Your recorded attendance across the current academic year.">
        <a href="{{ route('portals.student.dashboard') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            Dashboard
        </a>
    </x-page-header>

    <x-card title="Attendance records" :badges="[['text' => $records->count().' records', 'class' => 'badge-neutral']]">
        @forelse ($records as $record)
            <div class="list-row">
                <span class="badge {{ match (strtolower((string) $record->status)) {
                    'present' => 'badge-success',
                    'absent' => 'badge-danger',
                    'late' => 'badge-warning',
                    default => 'badge-neutral',
                } }}">
                    {{ ucfirst((string) $record->status) }}
                </span>
                <div class="text-sm">
                    {{ $record->session?->classRoom?->name }}
                    <div class="text-xs text-light">{{ $record->session?->date?->format('D, M j, Y') }}</div>
                </div>
                @if ($record?->session?->closed_at)
                    <time class="text-xs text-light">Closed {{ $record->session->closed_at->format('H:i') }}</time>
                @endif
            </div>
        @empty
            <x-empty-state icon="clipboard" title="No attendance records" message="There are no recorded sessions for you yet." />
        @endforelse
    </x-card>
</x-layouts.app>

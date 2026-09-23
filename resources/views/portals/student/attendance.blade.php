<x-layouts.app :title="__('My attendance')">
    <x-page-header
        :title="__('My attendance')"
        :description="__('Your recorded attendance across the current academic year.')">
        <a href="{{ route('cms.student.dashboard') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Dashboard') }}
        </a>
    </x-page-header>

    <x-card :title="__('Attendance records')">
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
                    <time class="text-xs text-light">{{ __('Closed') }} {{ $record->session->closed_at->format('H:i') }}</time>
                @endif
            </div>
        @empty
            <x-empty-state icon="clipboard" :title="__('No attendance records')" :message="__('There are no recorded sessions for you yet.')" />
        @endforelse
    </x-card>
</x-layouts.app>

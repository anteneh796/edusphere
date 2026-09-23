<x-layouts.app :title="__('Attendance')">
    <x-page-header :title="__('Attendance')" :description="__('Open and manage attendance sessions for your classes.')" />

    @if ($classRooms->isEmpty())
        <x-empty-state icon="clipboard-check" :title="__('No classes assigned')" :message="__('Attendance sessions are available once a class is assigned to you.')" />
    @else
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3" style="align-items:start;">
            <x-card :title="__('Open a new session')">
                <form method="POST" action="{{ route('cms.teacher.attendance.store') }}" class="grid gap-4">
                    @csrf

                    <x-select
                        name="class_room_id"
                        :label="__('Class')"
                        :options="$classRooms->mapWithKeys(fn ($room) => [$room->getKey() => $room->name . ($room->gradeLevel ? ' · ' . $room->gradeLevel->name : '')])"
                        placeholder="{{ __('Select class…') }}"
                        required />

                    <x-input
                        name="date"
                        type="date"
                        :label="__('Date')"
                        :value="old('date', now()->toDateString())"
                        required />

                    <div>
                        <button type="submit" class="btn btn-primary">{{ __('Open session') }}</button>
                    </div>
                </form>
            </x-card>

            <x-card :title="__('Recent sessions')" subtitle="{{ $currentYear?->name ?? __('Academic year') }}">
                @forelse ($sessions as $session)
                    <div class="flex items-center justify-between gap-4 py-3 border-b border-border last:border-0">
                        <a href="{{ route('cms.teacher.attendance.session', $session) }}" class="hover:underline">
                            <p class="font-medium">{{ $session->classRoom?->name ?? '—' }}</p>
                            <p class="text-sm text-foreground-muted">{{ $session->date?->format('D, M j, Y') ?? '—' }}</p>
                        </a>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-neutral">{{ $session->records_count }} {{ __('marked') }}</span>
                            <x-badge :color="$session->status?->badgeColor()" :dot="true">{{ $session->status?->label() }}</x-badge>
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="clipboard-check" :title="__('No sessions yet')" :message="__('Open a session above to start marking attendance.')" />
                @endforelse
            </x-card>
        </div>
    @endif
</x-layouts.app>
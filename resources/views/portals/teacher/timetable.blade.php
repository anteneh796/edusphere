<x-layouts.app :title="__('Timetable')">
    <x-page-header :title="__('Timetable')" :description="__('Your weekly teaching timetable.')">
        <a href="{{ route('cms.teacher.timetable') }}" class="btn btn-secondary">{{ __('This week') }}</a>
    </x-page-header>

    @php
        $dayLabels = [
            1 => __('Mon'),
            2 => __('Tue'),
            3 => __('Wed'),
            4 => __('Thu'),
            5 => __('Fri'),
        ];

        $slotMap = $slots->mapWithKeys(fn ($slot) => [$slot->period_number.'|'.$slot->day_of_week => $slot]);
    @endphp

    <x-card :title="__('Weekly timetable')">
        @if ($slots->isEmpty())
            <x-empty-state icon="calendar" :title="__('No timetable slots yet')" :message="__('Your scheduled periods will appear here.')" />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Period') }}</th>
                            @foreach ($days as $day)
                                <th>{{ $dayLabels[$day] ?? $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periods as $period)
                            <tr>
                                <td class="text-sm text-muted">{{ $period }}</td>
                                @foreach ($days as $day)
                                    @php($slot = $slotMap->get($period.'|'.$day))
                                    <td>
                                        @if ($slot)
                                            <div style="font-weight: var(--weight-medium);">
                                                @if ($slot->classSubject)
                                                    <a href="{{ route('cms.teacher.classes.show', $slot->classSubject) }}" class="hover:underline">
                                                        {{ $slot->classSubject->subject?->name ?? '—' }}
                                                    </a>
                                                @else
                                                    {{ $slot->classRoom?->name ?? '—' }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-muted">
                                                {{ $slot->classRoom?->name ?? '' }}
                                                @if ($slot->room)
                                                    · {{ $slot->room }}
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($days) + 1 }}">
                                    <x-empty-state icon="calendar" :title="__('No timetable slots yet')" :message="__('Your scheduled periods will appear here.')" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</x-layouts.app>
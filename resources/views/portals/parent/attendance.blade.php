<x-layouts.app :title="__('Attendance')">
    <x-page-header
        :title="__('Attendance')"
        :description="$ward ? __('Attendance for :name', ['name' => $ward->full_name]) : __('Attendance')">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    @if (! $ward)
        <x-card>
            <x-empty-state icon="users" :title="__('No child selected')" :message="__('Link or select a child to view their attendance.')" />
        </x-card>
    @else
        <div class="grid grid-stats">
            <x-stat-card :label="__('Present')" :value="number_format($summary['present'] ?? 0)" icon="check-circle" color="success" />
            <x-stat-card :label="__('Late')" :value="number_format($summary['late'] ?? 0)" icon="clock" color="warning" />
            <x-stat-card :label="__('Absent')" :value="number_format($summary['absent'] ?? 0)" icon="alert-triangle" color="danger" />
            <x-stat-card :label="__('Excused')" :value="number_format($summary['excused'] ?? 0)" icon="shield-check" color="info" />
        </div>

        <div class="grid grid-2 mt-4" style="align-items:start;">
            <x-card :title="__('Monthly overview')">
                <div class="flex items-center justify-between mb-3">
                    @php
                        $prevMonth = \Illuminate\Support\Carbon::parse($month . '-01')->subMonth()->format('Y-m');
                        $nextMonth = \Illuminate\Support\Carbon::parse($month . '-01')->addMonth()->format('Y-m');
                    @endphp
                    <a href="{{ route('cms.parent.attendance', ['month' => $prevMonth]) }}" class="btn btn-ghost btn-sm">
                        <x-icon name="chevron-left" class="icon-sm" />
                    </a>
                    <span class="font-medium">{{ \Illuminate\Support\Carbon::parse($month . '-01')->format('F Y') }}</span>
                    <a href="{{ route('cms.parent.attendance', ['month' => $nextMonth]) }}" class="btn btn-ghost btn-sm">
                        <x-icon name="chevron-right" class="icon-sm" />
                    </a>
                </div>

                @php
                    $firstDay = \Illuminate\Support\Carbon::parse($month . '-01');
                    $daysInMonth = $firstDay->daysInMonth;
                    $leading = $firstDay->dayOfWeek % 7;
                    $statusStyles = ['present' => 'bg-green-100 text-green-800', 'late' => 'bg-yellow-100 text-yellow-800', 'absent' => 'bg-red-100 text-red-800', 'excused' => 'bg-blue-100 text-blue-800'];
                    $legend = ['present' => 'Present', 'late' => 'Late', 'absent' => 'Absent', 'excused' => 'Excused'];
                @endphp

                <div class="grid grid-cols-7 gap-1 text-center">
                    @foreach (['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $day)
                        <div class="text-xs text-light py-1">{{ $day }}</div>
                    @endforeach
                    @for ($i = 0; $i < $leading; $i++)
                        <div></div>
                    @endfor
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $date = $firstDay->copy()->day($day)->format('Y-m-d');
                            $status = $calendar[$date] ?? null;
                        @endphp
                        <div class="aspect-square rounded-md flex items-center justify-center text-sm {{ $status ? ($statusStyles[$status] ?? 'bg-gray-100') : 'text-light' }}">
                            {{ $day }}
                        </div>
                    @endfor
                </div>

                <div class="flex flex-wrap gap-3 mt-3 text-xs text-light">
                    @foreach ($legend as $key => $label)
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full {{ $key === 'present' ? 'bg-green-500' : ($key === 'late' ? 'bg-yellow-500' : ($key === 'absent' ? 'bg-red-500' : 'bg-blue-500')) }}"></span>
                            {{ $label }}
                        </span>
                    @endforeach
                </div>
            </x-card>

            <x-card :title="__('Attendance records')">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Session') }}</th>
                            <th class="text-right">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            @php
                                $statusMap = ['present' => ['success', __('Present')], 'late' => ['warning', __('Late')], 'absent' => ['danger', __('Absent')], 'excused' => ['info', __('Excused')]];
                            @endphp
                            <tr>
                                <td>{{ $record->session?->date?->format('d M Y') ?? '—' }}</td>
                                <td>{{ $record->session?->name ?? '—' }}</td>
                                <td class="text-right">
                                    <span class="badge badge-{{ ($statusMap[$record->status] ?? [null])[0] ?? 'neutral' }}">{{ ($statusMap[$record->status] ?? [])[1] ?? ucfirst($record->status ?? '') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state" style="padding: var(--space-6);">
                                        <div class="empty-state-icon"><x-icon name="clipboard-check" class="icon-lg" /></div>
                                        <p>{{ __('No attendance records for this month.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>
        </div>
    @endif
</x-layouts.app>
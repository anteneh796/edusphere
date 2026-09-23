<x-layouts.app :title="__('Report card')">
    <x-page-header
        :title="__('Report card')"
        :description="$ward ? __('Overall performance of :name', ['name' => $ward->full_name]) : __('Report card')">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    @if (! $ward)
        <x-card>
            <x-empty-state icon="users" :title="__('No child selected')" :message="__('Link or select a child to view the report card.')" />
        </x-card>
    @else
        <div class="grid grid-stats">
            <x-stat-card :label="__('Subjects')" :value="$subjects->count()" icon="book-open" color="primary" />
            <x-stat-card :label="__('Results')" :value="$subjects->sum(fn ($rows) => $rows->count())" icon="clipboard-check" color="info" />
            <x-stat-card :label="__('Total')" :value="isset($totals['max']) && $totals['max'] > 0 ? number_format($totals['obtained'], 2).' / '.number_format($totals['max'], 2) : '—'" icon="receipt" color="warning" />
            <x-stat-card :label="__('Average')" :value="$totals['average'] === null ? '—' : number_format($totals['average'], 2).'%'" icon="bar-chart" color="success" />
            <x-stat-card :label="__('Attendance')" :value="isset($attendance['percentage']) ? number_format($attendance['percentage'], 1).'%' : '—'" icon="calendar" color="neutral" />
        </div>

        <div class="grid gap-4 mt-4">
            @forelse ($subjects as $subjectName => $grades)
                @php
                    $rows = $grades->values();
                    $count = $rows->count();
                    $percentage = $rows
                        ->map(function ($grade) {
                            $max = $grade->assessment?->total_marks ?? $grade->examSubject?->max_marks ?? 0;
                            $obtained = $grade->marks_obtained ?? 0;

                            return $max > 0 ? round(($obtained / $max) * 100, 1) : null;
                        })
                        ->filter(fn ($value) => $value !== null)
                        ->values();
                    $average = $percentage->isEmpty() ? null : round($percentage->avg(), 1);
                @endphp
                <x-card :title="$subjectName ?: __('General')">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="badge badge-accent">{{ __('Average') }}: {{ $average === null ? '—' : $average.'%' }}</span>
                        <span class="badge badge-neutral">{{ $count }} {{ Str::plural('entry', $count) }}</span>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Item') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th class="text-right">{{ __('Score') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $grade)
                                <tr>
                                    <td>{{ $grade->assessment?->title ?? $grade->examSubject?->subject?->name }}</td>
                                    <td>{{ $grade->assessment ? __('Classroom') : __('Exam') }}</td>
                                    <td class="text-right">
                                        {{ $grade->marks_obtained ?? '—' }}
                                        @if ($grade->assessment?->total_marks)
                                            / {{ $grade->assessment->total_marks }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-card>
            @empty
                <x-card>
                    <x-empty-state icon="award" :title="__('No published results')" :message="__('Your child\u2019s report card fills in as grades are published.')" />
                </x-card>
            @endforelse
        </div>
    @endif
</x-layouts.app>
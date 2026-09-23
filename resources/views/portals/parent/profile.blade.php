<x-layouts.app :title="__('Child profile')">
    <x-page-header
        :title="$ward->full_name"
        :description="__('Student :number', ['number' => $ward->student_number])">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    <div class="grid grid-stats">
        <x-stat-card :label="__('Attendance rate')" :value="isset($attendance['percentage']) ? number_format($attendance['percentage'], 1).'%' : '—'" icon="clipboard-check" color="success" />
        <x-stat-card :label="__('Results')" :value="$recentGrades->count()" icon="award" color="primary" />
        <x-stat-card :label="__('Class')" :value="$ward->classRoom?->name ?? '—'" icon="school" color="info" />
        <x-stat-card :label="__('Status')" :value="$ward->statusLabel()" icon="shield-check" color="neutral" />
    </div>

    <div class="grid grid-2 mt-4">
        <x-card :title="__('Student details')">
            <x-table
                :columns="[__('Field'), __('Value')]"
                :rows="collect([
                    [__('Student number'), $ward->student_number],
                    [__('Full name'), $ward->full_name],
                    [__('Date of birth'), $ward->date_of_birth?->format('d M Y') ?? '—'],
                    [__('Gender'), ucfirst($ward->gender ?? '—')],
                    [__('Grade'), $ward->gradeLevel?->name ?? '—'],
                    [__('Class room'), $ward->classRoom?->name ?? '—'],
                    [__('Address'), $ward->address ?? '—'],
                    [__('Health notes'), $ward->health_notes ?? '—'],
                ])" />
        </x-card>

        <x-card :title="__('Recent grades')">
            @forelse ($recentGrades as $grade)
                <div class="list-row">
                    <x-icon name="award" class="icon-sm text-light" />
                    <div class="min-w-0 flex-1">
                        <div class="truncate">{{ $grade->assessment?->title ?? $grade->examSubject?->subject?->name }}</div>
                        <div class="text-xs text-light">{{ $grade->assessment?->classSubject?->subject?->name ?? $grade->examSubject?->subject?->name }} · {{ $grade->updated_at?->diffForHumans() }}</div>
                    </div>
                    <span class="badge badge-success">{{ $grade->marks_obtained ?? '—' }}</span>
                </div>
            @empty
                <x-empty-state icon="award" :title="__('No grades yet')" :message="__('Published grades will appear here.')" />
            @endforelse
        </x-card>
    </div>

    <div class="mt-4">
        <x-card :title="__('Recent attendance')">
            @forelse ($recentAttendance as $record)
                <div class="list-row">
                    <x-icon name="clipboard-check" class="icon-sm text-light" />
                    <div class="min-w-0 flex-1">
                        <div class="truncate">{{ $record->session?->date?->format('d M Y') ?? '—' }}</div>
                        <div class="text-xs text-light">{{ $record->session?->name }}</div>
                    </div>
                    @php
                        $statusMap = ['present' => ['success', __('Present')], 'late' => ['warning', __('Late')], 'absent' => ['danger', __('Absent')], 'excused' => ['info', __('Excused')]];
                    @endphp
                    <span class="badge badge-{{ ($statusMap[$record->status] ?? [null])[0] ?? 'neutral' }}">{{ ($statusMap[$record->status] ?? [lcfirst($record->status ?? '—')])[1] ?? ucfirst($record->status ?? '') }}</span>
                </div>
            @empty
                <x-empty-state icon="clipboard-check" :title="__('No attendance yet')" :message="__('Attendance sessions will appear here.')" />
            @endforelse
        </x-card>
    </div>
</x-layouts.app>
<x-layouts.app :title="__('Attendance dashboard')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Overview')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.dashboard'])

    <x-page-header :title="__('Attendance overview')"
        :description="__('Daily attendance for :date', ['date' => \Carbon\Carbon::parse($today['date'])->format('D, M j, Y')])">
        <span style="display:inline-flex; align-items:center; gap: var(--space-2); flex-wrap:wrap;">
            @can('create', \App\Domains\Attendance\Models\AttendanceSession::class)
                <a href="{{ route('attendance.create') }}" class="btn btn-primary">
                    <x-icon name="clipboard-check" class="icon-sm" />
                    {{ __('Take attendance') }}
                </a>
            @endcan
            <a href="{{ route('attendance.reports.daily', ['date' => $today['date']]) }}" class="btn btn-secondary">
                <x-icon name="bar-chart" class="icon-sm" />
                {{ __('Daily report') }}
            </a>
        </span>
    </x-page-header>

    <div class="grid grid-stats mt-4">
        <x-stat-card :label="__('Sessions today')" :value="$today['sessions_count']" icon="clipboard-check" color="primary" />
        <x-stat-card :label="__('Open sessions')" :value="$today['open_count']" icon="clock" color="warning" />
        <x-stat-card :label="__('Submitted & locked')" :value="$today['closed_count']" icon="lock" color="neutral" />
        <x-stat-card :label="__('Records marked')" :value="$today['marked']" icon="users" color="info" />
        <x-stat-card :label="__('Attendance rate')" :value="$today['rate'] !== null ? $today['rate'].'%' : '—'" icon="trending-up" color="success" />
        <x-stat-card :label="__('Absences')" :value="$today['absent']" icon="alert-triangle" color="danger" />
    </div>

    <div class="dashboard-grid mt-4">
        <x-card :title="__('By grade')" subtitle="{{ __('Today’s mark distribution per grade level') }}" style="grid-column: span 2;">
            <div class="flex gap-1 flex-wrap">
                @forelse ($today['by_grade'] as $gradeId => $sessions)
                    @php
                        $gradeName = $sessions->first()->classRoom?->gradeLevel?->name ?? __('Ungraded');
                        $summary = \App\Domains\Attendance\Services\AttendanceService::summarize($sessions->flatMap(fn ($s) => $s->records));
                        $marked = array_sum($summary);
                        $rate = $marked > 0 ? round((($summary['present'] + $summary['late'] + $summary['excused']) / $marked) * 100, 1) : null;
                    @endphp
                    <a href="{{ route('attendance.reports.daily', ['date' => $today['date'], 'grade_level_id' => $gradeId === 'none' ? '' : $gradeId]) }}"
                        style="flex: 1 1 140px; min-width: 140px; padding: var(--space-2); border: 1px solid var(--color-border); border-radius: var(--radius); text-decoration: none; color: var(--color-text);">
                        <div style="font-size: var(--text-xl); font-weight: var(--weight-bold);">{{ $sessions->count() }}</div>
                        <div class="text-xs text-muted">{{ $gradeName }}</div>
                        <div class="text-xs text-muted">{{ $marked }} marked · {{ $rate !== null ? $rate.'%' : '—' }}</div>
                    </a>
                @empty
                    <x-empty-state icon="clipboard-check" :title="__('No sessions today')"
                        :message="__('Mark today’s attendance to see the breakdown here.')" />
                @endforelse
            </div>
        </x-card>

        <x-card :title="__('Attention')" subtitle="{{ __('Flags worth reviewing today') }}">
            <div style="display:flex; flex-direction:column; gap: var(--space-2);">
                <a href="{{ route('attendance.alerts') }}" class="btn btn-secondary" style="justify-content: space-between;">
                    <span style="display:inline-flex; align-items:center; gap: var(--space-1);">
                        <x-icon name="alert-triangle" class="icon-sm" />
                        {{ __('Student alerts') }}
                    </span>
                    <span class="badge badge-danger">{{ $today['absent'] }}</span>
                </a>
                @if (auth()->user()->hasPermission('attendance.approve'))
                    <a href="{{ route('attendance.corrections') }}" class="btn btn-secondary" style="justify-content: space-between;">
                        <span style="display:inline-flex; align-items:center; gap: var(--space-1);">
                            <x-icon name="refresh" class="icon-sm" />
                            {{ __('Pending corrections') }}
                        </span>
                        <span class="badge badge-neutral">{{ $today['open_count'] }}</span>
                    </a>
                @endif
            </div>
        </x-card>
    </div>

    <x-card :title="__('Today’s sessions')" subtitle="{{ __('All sessions opened for today, newest first') }}" class="mt-4">
        <x-slot:actions>
            <a href="{{ route('attendance.index', ['date' => $today['date']]) }}" class="btn btn-ghost btn-sm">{{ __('View all') }}</a>
        </x-slot:actions>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Class') }}</th>
                        <th>{{ __('Grade') }}</th>
                        <th>{{ __('Marked') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($today['sessions'] as $session)
                        @php($summary = \App\Domains\Attendance\Services\AttendanceService::summarize($session->records))
                        <tr>
                            <td>
                                <a href="{{ route('attendance.show', $session) }}" style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">
                                    {{ $session->classRoom?->name ?? '—' }}
                                </a>
                            </td>
                            <td class="text-sm text-muted">{{ $session->classRoom?->gradeLevel?->name ?? '—' }}</td>
                            <td>
                                <div class="att-summary">
                                    <span class="att-chip present">P · <b>{{ $summary['present'] }}</b></span>
                                    <span class="att-chip late">L · <b>{{ $summary['late'] }}</b></span>
                                    <span class="att-chip absent">A · <b>{{ $summary['absent'] }}</b></span>
                                    <span class="att-chip excused">E · <b>{{ $summary['excused'] }}</b></span>
                                </div>
                            </td>
                            <td>
                                <x-badge :color="$session->status?->badgeColor()" :dot="true">{{ $session->status?->label() }}</x-badge>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('attendance.show', $session) }}" class="btn btn-sm btn-secondary">
                                    {{ __('Open') }} <x-icon name="chevron-right" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="clipboard-check" :title="__('No sessions today')"
                                    :message="__('Start a session from “Take attendance” to record today’s marks.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>
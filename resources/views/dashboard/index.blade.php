<x-layouts.app>

    @php
        $today = \Carbon\CarbonImmutable::now()->locale(app()->getLocale());
        $hour = (int) \Carbon\Carbon::now()->format('G');
        $greeting = match (true) {
            $hour < 12 => __('Good morning'),
            $hour < 17 => __('Good afternoon'),
            default => __('Good evening'),
        };
    @endphp

    <div class="welcome-hero">
        <span class="hero-date">{{ $today->format('D, M j, Y') }}</span>
        <h2>{{ $greeting }}, {{ auth()->user()->first_name }}</h2>
        <p>
            {{ __("Welcome back to EduSphere. Here's a quick look at what's happening across your school today.") }}
        </p>
    </div>

    <div class="grid grid-stats mt-4">
        <x-stat-card :label="__('Total Students')" :value="number_format($counts['students'])" icon="graduation" color="primary" />
        <x-stat-card :label="__('Teachers')" :value="number_format($counts['teachers'])" icon="users" color="info" />
        <x-stat-card :label="__('Classes')" :value="number_format($counts['classes'])" icon="book-open" color="accent" />
        @if ($isSuperAdmin)
            <x-stat-card :label="__('System Users')" :value="number_format($accountStats['users'])" icon="user-check" color="success" />
            <x-stat-card :label="__('Defined Roles')" :value="number_format($accountStats['roles'])" icon="shield" color="danger" />
        @endif
    </div>

    @if ($canViewApprovals || $canViewNotifications)
        <div class="grid grid-stats mt-4">
            @if ($canViewApprovals)
                <x-stat-card :label="__('Attendance rate')" :value="$kpis['attendance_rate'] !== null ? $kpis['attendance_rate'].'%' : '—'" icon="clipboard-check" color="success" />
                <x-stat-card :label="__('Pending approvals')" :value="number_format($kpis['pending_approvals'])" icon="file-text" color="warning" />
            @endif
            @if ($canViewNotifications)
                <x-stat-card :label="__('Unread notifications')" :value="number_format($kpis['unread_notifications'])" icon="bell" color="accent" />
            @endif
        </div>
    @endif

    <div class="dashboard-grid mt-4">
        <div class="flex flex-col gap-3">
            @if ($canSeeAttendance)
                @php $todayTitle = __('Today') . ' ' . __('attendance'); @endphp
                <x-card :title="$todayTitle" :subtitle="__(':count session(s) recorded today.', ['count' => $attendance['sessions']])">
                    <div class="att-summary">
                        <span class="att-chip present">{{ __('Present') }} · <b>{{ $attendance['present'] }}</b></span>
                        <span class="att-chip late">{{ __('Late') }} · <b>{{ $attendance['late'] }}</b></span>
                        <span class="att-chip absent">{{ __('Absent') }} · <b>{{ $attendance['absent'] }}</b></span>
                        <span class="att-chip excused">{{ __('Excused') }} · <b>{{ $attendance['excused'] }}</b></span>
                    </div>
                    @can('create', \App\Domains\Attendance\Models\AttendanceSession::class)
                        <div class="mt-3">
                            <a href="{{ route('attendance.create') }}" class="btn btn-primary btn-sm">
                                <x-icon name="clipboard-check" class="icon-sm" />
                                {{ __('Take attendance') }}
                            </a>
                        </div>
                    @endcan
                </x-card>
            @endif

            @if ($canSeeExams)
                <x-card :title="__('Exams & Results')"
                    :subtitle="__(':published published · :draft draft · :entries result entries.', ['published' => $exams['published'], 'draft' => $exams['draft'], 'entries' => $exams['entries']])">
                    @can('create', \App\Domains\Exams\Models\Exam::class)
                        <div class="mt-3">
                            <a href="{{ route('exams.create') }}" class="btn btn-primary btn-sm">
                                <x-icon name="award" class="icon-sm" />
                                {{ __('New exam') }}
                            </a>
                            <a href="{{ route('exams.index') }}" class="btn btn-secondary btn-sm">
                                {{ __('All exams') }}
                            </a>
                        </div>
                    @else
                        <div class="mt-3">
                            <a href="{{ route('exams.index') }}" class="btn btn-secondary btn-sm">
                                {{ __('All exams') }}
                            </a>
                        </div>
                    @endcan
                </x-card>
            @endif

            <x-card :title="__('Quick actions')">
                <div class="quick-actions">
                    @foreach ($quickLinks as $link)
                        <a href="{{ $link['url'] }}" class="quick-action">
                            <span class="icon-wrap"><x-icon :name="$link['icon']" /></span>
                            {{ __($link['label']) }}
                        </a>
                    @endforeach
                </div>
            </x-card>
        </div>

        <div class="flex flex-col gap-3">
            @if ($canViewApprovals)
                <x-card :title="__('Approvals queue')" :subtitle="__('Latest requests awaiting sign-off')">
                    @if ($pendingApprovals->isEmpty())
                        <x-empty-state icon="file-text" :title="__('Nothing pending')" :message="__('No approval requests are waiting on you right now.')" />
                    @else
                        <div class="feed">
                            @foreach ($pendingApprovals as $approval)
                                <a href="{{ route('approvals.show', $approval) }}" class="feed-item" style="text-decoration:none;">
                                    <x-avatar :initials="optional($approval->requester)->initials() ?? '–'" size="sm" />
                                    <div style="min-width:0;">
                                        <div style="font-size: var(--text-sm); font-weight: var(--weight-semibold);">
                                            {{ $approval->typeLabel() }}
                                        </div>
                                        <div class="truncate" style="font-size: var(--text-xs); color: var(--color-text-muted); max-width: 200px;">
                                            {{ $approval->requester?->full_name ?? __('System') }} · {{ $approval->submitted_at?->diffForHumans() }}
                                        </div>
                                    </div>
                                    <x-badge color="warning">{{ __('Pending') }}</x-badge>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('approvals.index') }}" class="btn btn-secondary btn-sm">{{ __('All requests') }}</a>
                        </div>
                    @endif
                </x-card>
            @endif

            @if ($canViewNotifications)
                <x-card :title="__('Notifications')">
                    @if ($recentNotifications->isEmpty())
                        <x-empty-state icon="bell" :title="__('All caught up')" :message="__('New alerts will appear here.')" />
                    @else
                        <div class="feed">
                            @foreach ($recentNotifications as $notification)
                                <a href="{{ route('notifications.show', $notification) }}" class="feed-item" style="text-decoration:none;">
                                    <x-avatar :initials="$notification->isRead() ? 'OK' : '·'" size="sm" />
                                    <div style="min-width:0;">
                                        <div class="truncate" style="font-size: var(--text-sm); font-weight: var(--weight-semibold);">
                                            {{ $notification->title }}
                                        </div>
                                        <div class="truncate" style="font-size: var(--text-xs); color: var(--color-text-muted); max-width: 200px;">
                                            {{ $notification->body }}
                                        </div>
                                    </div>
                                    <time>{{ $notification->created_at->diffForHumans() }}</time>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm">{{ __('View all') }}</a>
                        </div>
                    @endif
                </x-card>
            @endif

            @if ($isSchoolAdmin)
                <x-card :title="__('Recent activity')">
                    @if ($recentLogs->isEmpty())
                        <x-empty-state icon="activity" :title="__('No activity yet')" :message="__('Audit entries will appear here as staff perform actions.')" />
                    @else
                        <div class="feed">
                            @foreach ($recentLogs as $log)
                                <div class="feed-item">
                                    <x-avatar :initials="optional($log->user)->initials() ?? '–'" size="sm" />
                                    <div style="min-width:0;">
                                        <div style="font-size: var(--text-sm); font-weight: var(--weight-semibold);">
                                            {{ $log->action }}
                                        </div>
                                        <div class="truncate" style="font-size: var(--text-xs); color: var(--color-text-muted); max-width: 200px;">
                                            {{ $log->module }} · {{ $log->user?->full_name ?? __('System') }}
                                        </div>
                                    </div>
                                    <time>{{ $log->created_at->diffForHumans() }}</time>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-card>
            @endif
        </div>
    </div>

</x-layouts.app>
<x-layouts.app>

    @php
        $today = \Carbon\CarbonImmutable::now()->locale('en');
        $hour = (int) \Carbon\Carbon::now()->format('G');
        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };
    @endphp

    <div class="welcome-hero">
        <span class="hero-date">{{ $today->format('D, M j, Y') }}</span>
        <h2>{{ $greeting }}, {{ auth()->user()->first_name }}</h2>
        <p>
            Welcome back to EduSphere. Here's a quick look at what's happening across your school today.
        </p>
    </div>

    <div class="grid grid-stats mt-4">
        <x-stat-card label="Total Students" value="{{ number_format($counts['students']) }}" icon="graduation" color="primary" />
        <x-stat-card label="Teachers" value="{{ number_format($counts['teachers']) }}" icon="users" color="info" />
        <x-stat-card label="Classes" value="{{ number_format($counts['classes']) }}" icon="book-open" color="accent" />
        <x-stat-card label="Open Invoices" value="{{ number_format($counts['invoices']) }}" icon="receipt" color="warning" />
        <x-stat-card label="System Users" value="{{ number_format($accountStats['users']) }}" icon="user-check" color="success" />
        <x-stat-card label="Defined Roles" value="{{ number_format($accountStats['roles']) }}" icon="shield" color="danger" />
    </div>

    <div class="dashboard-grid mt-4">
        <div class="flex flex-col gap-3">
            <x-card title="Today's attendance" :subtitle="$attendance['sessions'] . ' session(s) recorded today.'">
                <div class="att-summary">
                    <span class="att-chip present">Present · <b>{{ $attendance['present'] }}</b></span>
                    <span class="att-chip late">Late · <b>{{ $attendance['late'] }}</b></span>
                    <span class="att-chip absent">Absent · <b>{{ $attendance['absent'] }}</b></span>
                    <span class="att-chip excused">Excused · <b>{{ $attendance['excused'] }}</b></span>
                </div>
                @can('create', \App\Domains\Attendance\Models\AttendanceSession::class)
                    <div class="mt-3">
                        <a href="{{ route('attendance.create') }}" class="btn btn-primary btn-sm">
                            <x-icon name="clipboard-check" class="icon-sm" />
                            Take attendance
                        </a>
                    </div>
                @endcan
            </x-card>

            <x-card title="Exams & Results"
                subtitle="{{ $exams['published'] }} published · {{ $exams['draft'] }} draft · {{ $exams['entries'] }} result entries.">
                @can('create', \App\Domains\Exams\Models\Exam::class)
                    <div class="mt-3">
                        <a href="{{ route('exams.create') }}" class="btn btn-primary btn-sm">
                            <x-icon name="award" class="icon-sm" />
                            New exam
                        </a>
                        <a href="{{ route('exams.index') }}" class="btn btn-secondary btn-sm">
                            All exams
                        </a>
                    </div>
                @else
                    <div class="mt-3">
                        <a href="{{ route('exams.index') }}" class="btn btn-secondary btn-sm">
                            All exams
                        </a>
                    </div>
                @endcan
            </x-card>

            <x-card title="Quick actions">
                <div class="quick-actions">
                    @foreach ($quickLinks as $link)
                        <a href="{{ $link['url'] }}" class="quick-action">
                            <span class="icon-wrap"><x-icon :name="$link['icon']" /></span>
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </x-card>
        </div>

        <div class="flex flex-col gap-3">
            <x-card title="Recent activity">
                @if ($recentLogs->isEmpty())
                    <x-empty-state icon="activity" title="No activity yet" message="Audit entries will appear here as staff perform actions." />
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
                                        {{ $log->module }} · {{ $log->user?->full_name ?? 'System' }}
                                    </div>
                                </div>
                                <time>{{ $log->created_at->diffForHumans() }}</time>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>
    </div>

</x-layouts.app>
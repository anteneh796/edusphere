<x-layouts.app :title="'Attendance'">
    <x-breadcrumb :items="[['label' => 'Attendance']]" />

    <x-page-header title="Attendance" description="Take and review daily attendance by class.">
        @can('create', \App\Domains\Attendance\Models\AttendanceSession::class)
            <a href="{{ route('attendance.create') }}" class="btn btn-primary">
                <x-icon name="clipboard-check" class="icon-sm" />
                Take attendance
            </a>
        @endcan
    </x-page-header>

    <div class="grid grid-cards" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <x-stat-card label="Open sessions" :value="$openCount" icon="clipboard" color="primary" />
        <x-stat-card label="Sessions today" :value="$todayCount" icon="calendar" color="success" />
        <x-stat-card label="Current year" value="{{ $currentYear->name }}" icon="school" color="info" />
    </div>

    <x-card class="mt-4">
        <form method="GET" action="{{ route('attendance.index') }}" class="filter-grid">
            <div class="form-group">
                <label class="form-label" for="class-filter">Class</label>
                <select id="class-filter" name="class_room_id" class="form-select">
                    <option value="">All classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected(request('class_room_id') === $class->id)>
                            {{ $class->name }} · {{ $class->gradeLevel->name ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="date-filter">Date</label>
                <input type="date" id="date-filter" name="date" class="form-input" value="{{ request('date') }}" />
            </div>

            <div class="form-group">
                <label class="form-label" for="status-filter">Status</label>
                <select id="status-filter" name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach (\App\Support\Enums\AttendanceSessionStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-1" style="align-items:end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="search" class="icon-sm" />
                    Filter
                </button>
                @if (request('class_room_id') || request('date') || request('status'))
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Marked</th>
                        <th>Taken by</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $session->classRoom->name }}</div>
                                <div class="text-xs text-muted">{{ $session->classRoom->gradeLevel->name ?? '' }} · {{ $session->classRoom->academicYear->name ?? '' }}</div>
                            </td>
                            <td class="text-sm">{{ $session->date->format('D, M j, Y') }}</td>
                            <td>
                                <x-badge :color="$session->status?->badgeColor()" :dot="true">{{ $session->status?->label() }}</x-badge>
                            </td>
                            <td class="text-sm">{{ $session->records_count }} student(s)</td>
                            <td class="text-sm">{{ $session->takenBy?->name ?? '—' }}</td>
                            <td class="actions-cell">
                                <a href="{{ route('attendance.show', $session) }}" class="btn btn-ghost btn-sm btn-icon" title="View">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="clipboard-check" title="No attendance sessions found" message="Take attendance for a class to get started.">
                                    @can('create', \App\Domains\Attendance\Models\AttendanceSession::class)
                                        <a href="{{ route('attendance.create') }}" class="btn btn-primary btn-sm">Take attendance</a>
                                    @endcan
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $sessions->links() }}
        </div>
    </x-card>
</x-layouts.app>
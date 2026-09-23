<x-layouts.app :title="__('Class Roster')">
    <x-breadcrumb :items="[
        ['label' => __('Students'), 'url' => route('students.index')],
        ['label' => __('Class Roster')],
    ]" />

    <x-page-header :title="__('Class Roster')" :description="__('Printable class lists, updated automatically after transfers and promotion.')">
        @if ($selected)
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <x-icon name="printer" class="icon-sm" />
                {{ __('Print') }}
            </button>
        @endif
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('students.roster') }}" class="filter-grid">
            <div class="form-group">
                <label class="form-label" for="class-filter">{{ __('Class') }}</label>
                <select id="class-filter" name="class_room_id" class="form-select">
                    <option value="">{{ __('Select class') }}…</option>
                    @foreach ($classRooms as $class)
                        <option value="{{ $class->id }}" @selected($selected && $selected->id === $class->id)>
                            {{ $class->gradeLevel->name }} · {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-1" style="align-items:end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="search" class="icon-sm" />
                    {{ __('View roster') }}
                </button>
                @if ($selected)
                    <a href="{{ route('students.roster') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>
    </x-card>

    @if ($selected)
        <x-card>
            <div class="card-header">
                <div>
                    <h3 class="card-title">{{ $selected->gradeLevel->name }} · {{ $selected->name }} — {{ $year->name }}</h3>
                    <div class="card-subtitle mt-1">{{ __(':count students enrolled', ['count' => $students->count()]) }}</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:64px;">{{ __('Roll') }}</th>
                            <th>{{ __('Student') }}</th>
                            <th>{{ __('Gender') }}</th>
                            <th>{{ __('Student ID') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $enrollment)
                            @php($student = $enrollment->student)
                            <tr>
                                <td><span class="code-chip">{{ $enrollment->roll_number }}</span></td>
                                <td>
                                    <div class="avatar-cell">
                                        <x-avatar :initials="$student->initials()" size="sm" />
                                        <div style="min-width:0;">
                                            <a href="{{ route('students.show', $student) }}" style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">{{ $student->full_name }}</a>
                                            <div class="text-xs text-muted">{{ $student->statusLabel() }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-sm">{{ \Illuminate\Support\Str::headline($student->gender) }}</td>
                                <td><span class="code-chip">{{ $student->student_number }}</span></td>
                                <td>
                                    <x-badge :color="$student->statusBadgeColor()">{{ $student->statusLabel() }}</x-badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state icon="users" :title="__('No students in this class')" message="Students appear here when they are enrolled in this section." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <style media="print">
            .sidebar, .app-topbar, .breadcrumb-wrapper, .page-header, .btn, form, .card-header { display: none !important; }
            .app-body, .app-main, .app-content { padding: 0 !important; margin: 0 !important; }
        </style>
    @endif

</x-layouts.app>
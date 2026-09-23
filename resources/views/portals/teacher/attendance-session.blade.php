<x-layouts.app :title="__('Attendance · ') . $session->classRoom?->name . ' · ' . $session->date?->format('M j, Y')">

    @php($statusOptions = \App\Support\Enums\AttendanceStatus::cases())

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('cms.teacher.attendance')],
        ['label' => $session->classRoom?->name . ' · ' . $session->date?->format('M j, Y')],
    ]" />

    <x-page-header
        :title="$session->classRoom?->name"
        :description="($session->classRoom?->gradeLevel?->name ? $session->classRoom->gradeLevel->name . ' · ' : '') . __('daily attendance for :date', ['date' => $session->date?->format('D, M j, Y')])">
        <span style="display:inline-flex; align-items:center; gap: var(--space-2); flex-wrap:wrap;">
            <x-badge :color="$session->status?->badgeColor()" :dot="true">{{ $session->status?->label() }}</x-badge>
            @if ($session->isLocked())
                <x-badge color="neutral">{{ __('Locked') }}</x-badge>
            @endif
            @if ($session->isOpen())
                <form method="POST" action="{{ route('cms.teacher.attendance.close', $session) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <x-icon name="lock" class="icon-sm" />
                        {{ __('Submit session') }}
                    </button>
                </form>
            @endif
            <a href="{{ route('cms.teacher.attendance') }}" class="btn btn-secondary">
                <x-icon name="chevron-left" class="icon-sm" />
                {{ __('All sessions') }}
            </a>
        </span>
    </x-page-header>

    @if (session('errors')?->has('correction') && session('errors')->first('correction'))
        <x-alert type="danger">{{ session('errors')->first('correction') }}</x-alert>
    @endif

    <div class="grid" style="grid-template-columns: 1fr 300px; gap: var(--space-4); align-items:start;">
        <x-card :title="__('Marking board')">
            @if (empty($boardRows))
                <x-empty-state icon="users" :title="__('No students in this class')" :message="__('Enroll students into this class first.')" />
            @else
                @if ($session->isOpen())
                    <form method="POST" action="{{ route('cms.teacher.attendance.update', $session) }}">
                        @csrf
                        @method('PUT')

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Student') }}</th>
                                        <th>{{ __('Student no.') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Note') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($boardRows as $i => $student)
                                        <tr>
                                            <td class="text-sm text-muted">{{ $i + 1 }}</td>
                                            <td class="text-sm">{{ $student['name'] }}</td>
                                            <td><span class="code-chip">{{ $student['number'] }}</span></td>
                                            <td>
                                                <select name="records[{{ $i }}][status]" class="form-select" data-att-status-select>
                                                    @foreach ($statusOptions as $option)
                                                        <option value="{{ $option->value }}"
                                                            @selected(old("records.$i.status", $student['status']) === $option->value)>
                                                            {{ $option->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="records[{{ $i }}][note]"
                                                    class="form-control"
                                                    placeholder="{{ __('Note…') }}"
                                                    value="{{ old("records.$i.note", $student['note']) }}" />
                                                <input type="hidden" name="records[{{ $i }}][student_id]" value="{{ $student['student_id'] }}" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex" style="justify-content: space-between; align-items:center; flex-wrap:wrap; gap: var(--space-2);">
                            <div style="display:flex; align-items:center; gap: var(--space-2); flex-wrap:wrap;">
                                <button type="button" class="btn btn-secondary" id="markAllPresentBtn">
                                    <x-icon name="check-circle" class="icon-sm" />
                                    {{ __('Mark all present') }}
                                </button>
                                <p class="text-sm text-muted">{{ __('Choose a status per student, then save.') }}</p>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <x-icon name="check" class="icon-sm" />
                                {{ __('Save attendance') }}
                            </button>
                        </div>
                    </form>

                    @push('scripts')
                        <script>
                            document.getElementById('markAllPresentBtn')?.addEventListener('click', () => {
                                document.querySelectorAll('select[data-att-status-select]').forEach((select) => {
                                    select.value = 'present';
                                });
                            });
                        </script>
                    @endpush
                @else
                    <div class="flex" style="justify-content:space-between; align-items:center; margin-bottom: var(--space-3); flex-wrap:wrap; gap: var(--space-2);">
                        <p class="text-sm text-muted">
                            {{ __('This session is locked. Direct edits are disabled — request a correction below for any student.') }}
                        </p>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Student') }}</th>
                                    <th>{{ __('Student no.') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Correction') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($boardRows as $i => $student)
                                    <tr>
                                        <td class="text-sm text-muted">{{ $i + 1 }}</td>
                                        <td class="text-sm">{{ $student['name'] }}</td>
                                        <td><span class="code-chip">{{ $student['number'] }}</span></td>
                                        <td>
                                            <x-badge :color="\App\Support\Enums\AttendanceStatus::tryFrom($student['status'])?->badgeColor()">
                                                {{ \App\Support\Enums\AttendanceStatus::tryFrom($student['status'])?->label() }}
                                            </x-badge>
                                        </td>
                                        <td>
                                            <details>
                                                <summary class="btn btn-sm btn-secondary" style="list-style:none;cursor:pointer;">
                                                    {{ __('Request correction') }}
                                                </summary>
                                                <form method="POST" action="{{ route('cms.teacher.attendance.correction', $session) }}" class="mt-2" style="max-width:320px;">
                                                    @csrf
                                                    <input type="hidden" name="student_id" value="{{ $student['student_id'] }}" />
                                                    <select name="requested_status" class="form-select" style="margin-bottom: var(--space-2);">
                                                        @foreach ($statusOptions as $option)
                                                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                                        @endforeach
                                                    </select>
                                                    <textarea name="reason" class="form-control" required placeholder="{{ __('Reason for correction…') }}" rows="2"></textarea>
                                                    <button type="submit" class="btn btn-sm btn-primary mt-2">
                                                        <x-icon name="send" class="icon-sm" />
                                                        {{ __('Submit request') }}
                                                    </button>
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </x-card>

        <div style="display:flex; flex-direction:column; gap: var(--space-4);">
            <x-card :title="__('Summary')">
                <div class="att-summary">
                    <span class="att-chip present">P · <b>{{ $summary['present'] }}</b></span>
                    <span class="att-chip late">L · <b>{{ $summary['late'] }}</b></span>
                    <span class="att-chip absent">A · <b>{{ $summary['absent'] }}</b></span>
                    <span class="att-chip excused">E · <b>{{ $summary['excused'] }}</b></span>
                </div>
            </x-card>

            <x-card :title="__('Details')">
                <dl class="detail-list">
                    <div>
                        <dt>{{ __('Class') }}</dt>
                        <dd>{{ $session->classRoom?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Academic year') }}</dt>
                        <dd>{{ $session->classRoom?->academicYear?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Date') }}</dt>
                        <dd>{{ $session->date?->format('D, M j, Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Taken by') }}</dt>
                        <dd>{{ $session->takenBy?->full_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Opened') }}</dt>
                        <dd>{{ $session->opened_at?->format('g:i A') ?? '—' }}</dd>
                    </div>
                    @if ($session->submitted_at)
                        <div>
                            <dt>{{ __('Submitted') }}</dt>
                            <dd>{{ $session->submitted_at?->format('M j, Y g:i A') }}</dd>
                        </div>
                    @endif
                    @if ($session->locked_at)
                        <div>
                            <dt>{{ __('Locked') }}</dt>
                            <dd>{{ $session->locked_at?->format('M j, Y g:i A') }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>
        </div>
    </div>
</x-layouts.app>
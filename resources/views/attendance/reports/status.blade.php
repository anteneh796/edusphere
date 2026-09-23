<x-layouts.app :title="__('Absence & lateness report')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Reports')],
        ['label' => __('Absence & lateness')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.reports.status'])
    @include('attendance.reports.partials._nav', ['activeTab' => 'attendance.reports.status'])

    <x-page-header :title="__(':status records', ['status' => $status->label()])"
        :description="__('All :status marks between :from and :to', ['status' => strtolower($status->label()), 'from' => \Carbon\Carbon::parse($from)->format('M j, Y'), 'to' => \Carbon\Carbon::parse($to)->format('M j, Y')])" />

    <x-card class="mt-4">
        <form method="GET" class="flex" style="gap: var(--space-2); flex-wrap: wrap; align-items: end;">
            <div>
                <label class="text-xs text-muted">{{ __('Status') }}</label>
                <select name="status" class="form-select">
                    @foreach (\App\Support\Enums\AttendanceStatus::cases() as $option)
                        <option value="{{ $option->value }}" @selected($status->value === $option->value)>{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-muted">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control" />
            </div>
            <div>
                <label class="text-xs text-muted">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control" />
            </div>
            <div>
                <label class="text-xs text-muted">{{ __('Class') }}</label>
                <select name="class_room_id" class="form-select">
                    <option value="">{{ __('All classes') }}</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected(request('class_room_id') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
        </form>
    </x-card>

    <x-card :title="$status->label().' records'" class="mt-4">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('No.') }}</th>
                        <th>{{ __('Class') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Note') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $record)
                        <tr>
                            <td>{{ $record->session?->date?->format('D, M j, Y') ?? '—' }}</td>
                            <td style="font-weight: var(--weight-semibold);">{{ $record->student?->full_name ?? '—' }}</td>
                            <td><span class="code-chip">{{ $record->student?->student_number ?? '' }}</span></td>
                            <td class="text-sm text-muted">{{ $record->session?->classRoom?->name ?? '—' }}</td>
                            <td><x-badge :color="$record->status?->badgeColor()">{{ $record->status?->label() }}</x-badge></td>
                            <td class="text-sm text-muted">{{ $record->note ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><x-empty-state icon="check-circle" :title="__('None found')" :message="__('No :status records match the filters.', ['status' => strtolower($status->label())])" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>
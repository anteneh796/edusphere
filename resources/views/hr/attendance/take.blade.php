<x-layouts.app :title="__('Take Attendance')">

    <x-page-header :title="__('Take Attendance')"
        :description="__('Record staff attendance for').' '.$date->format('M j, Y')">
        <a href="{{ route('hr.attendance.index', ['date' => $date->toDateString()]) }}" class="btn btn-secondary">{{ __('Back to attendance') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.attendance.store') }}">
            @csrf
            <div class="flex gap-1" style="align-items:flex-end; padding-bottom: var(--space-2);">
                <div class="form-group" style="margin:0;">
                    <label class="form-label" for="date">{{ __('Date') }}</label>
                    <input type="date" id="date" name="date" value="{{ $date->toDateString() }}" class="form-control" />
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Save attendance') }}</button>
            </div>

            <div class="table-responsive" style="border-top:1px solid var(--color-border);">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Position') }}</th>
                            <th style="width:220px;">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>
                                    <div style="font-weight: var(--weight-semibold);">{{ $employee->full_name }}</div>
                                    <div class="text-xs text-muted">{{ $employee->employee_id }}</div>
                                </td>
                                <td class="text-sm text-muted">{{ $employee->position?->name ?? '—' }}</td>
                                <td>
                                    <select name="statuses[{{ $employee->id }}]" class="form-select">
                                        <option value="">— {{ __('Not recorded') }} —</option>
                                        @foreach (\App\Support\Enums\StaffAttendanceStatus::cases() as $case)
                                            <option value="{{ $case->value }}"
                                                @selected(old('statuses.'.$employee->id, optional($records->get($employee->id))->status) === $case->value)>
                                                {{ $case->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <x-empty-state icon="users" :title="__('No active employees')" :message="__('Add staff before recording attendance.')" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer flex" style="justify-content:flex-end; gap: var(--space-1);">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Save attendance') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>
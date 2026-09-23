<x-layouts.app :title="__('Attendance Report')">

    <x-page-header :title="__('Attendance Report')" :description="__('Presence, lateness and absence per employee for the selected month.')">
        <a href="{{ route('hr.reports.index') }}" class="btn btn-secondary">{{ __('All reports') }}</a>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('hr.reports.attendance') }}" class="flex gap-1" style="align-items:flex-end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="month">{{ __('Month') }}</label>
                <input type="month" id="month" name="month" value="{{ $month }}" class="form-control" />
            </div>
            <button type="submit" class="btn btn-primary">{{ __('View') }}</button>
        </form>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        @foreach (\App\Support\Enums\StaffAttendanceStatus::cases() as $attendanceStatus)
                            <th class="text-center" style="min-width:70px;">{{ $attendanceStatus->short() }}</th>
                        @endforeach
                        <th class="text-center">{{ __('Days recorded') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summary as $employeeId => $rows)
                        @php $employee = $rows->first()->employee; @endphp
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $employee?->full_name ?? '—' }}</div>
                                <div class="text-xs text-muted">{{ $employee?->employee_id }}</div>
                            </td>
                            @foreach (\App\Support\Enums\StaffAttendanceStatus::cases() as $attendanceStatus)
                                @php $row = $rows->firstWhere('status', $attendanceStatus->value); @endphp
                                <td class="text-center">
                                    @if ($row)
                                        <x-badge :color="$attendanceStatus->badgeColor()">{{ $row->days }}</x-badge>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center" style="font-weight: var(--weight-semibold);">{{ $rows->sum('days') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state icon="calendar" :title="__('No attendance recorded')" :message="__('No records found for ').\Illuminate\Support\Carbon::parse($month.'-01')->format('F Y').'.'" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

</x-layouts.app>
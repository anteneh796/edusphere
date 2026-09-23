<x-layouts.app :title="__('Attendance settings')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Settings')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.settings'])

    <x-page-header :title="__('Attendance rules')"
        :description="__('Configure how attendance is taken, reported and alerted across the school')" />

    <x-card class="mt-4" style="max-width: 720px;">
        <form method="POST" action="{{ route('attendance.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-3); margin-bottom: var(--space-3);">
                <div>
                    <label class="form-label">{{ __('Attendance mode') }}</label>
                    <select name="attendance_mode" class="form-select">
                        <option value="daily" @selected($settings['attendance_mode'] === 'daily')>{{ __('Daily (one session per class)') }}</option>
                        <option value="period" @selected($settings['attendance_mode'] === 'period')>{{ __('By period (per subject)') }}</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">{{ __('Late threshold (minutes)') }}</label>
                    <input type="number" name="late_threshold_minutes" min="0" max="240"
                        value="{{ $settings['late_threshold_minutes'] }}" class="form-control" />
                </div>
                <div>
                    <label class="form-label">{{ __('Start time') }}</label>
                    <input type="time" name="start_time" value="{{ $settings['start_time'] }}" class="form-control" />
                </div>
                <div>
                    <label class="form-label">{{ __('End time') }}</label>
                    <input type="time" name="end_time" value="{{ $settings['end_time'] }}" class="form-control" />
                </div>
                <div>
                    <label class="form-label">{{ __('Absence alert threshold') }}</label>
                    <input type="number" name="absence_alert_threshold" min="1" max="99"
                        value="{{ $settings['absence_alert_threshold'] }}" class="form-control" />
                </div>
                <div>
                    <label class="form-label">{{ __('Lateness alert threshold') }}</label>
                    <input type="number" name="late_alert_threshold" min="1" max="99"
                        value="{{ $settings['late_alert_threshold'] }}" class="form-control" />
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap: var(--space-2); margin-bottom: var(--space-4);">
                <label class="form-label" style="display:flex; align-items:center; gap: var(--space-2);">
                    <input type="checkbox" name="excused_counts_as_present" value="1"
                        @checked($settings['excused_counts_as_present']) class="form-checkbox" />
                    {{ __('Excused counts as present in rates') }}
                </label>
                <label class="form-label" style="display:flex; align-items:center; gap: var(--space-2);">
                    <input type="checkbox" name="parent_absence_notification" value="1"
                        @checked($settings['parent_absence_notification']) class="form-checkbox" />
                    {{ __('Notify parents on absence') }}
                </label>
                <label class="form-label" style="display:flex; align-items:center; gap: var(--space-2);">
                    <input type="checkbox" name="correction_approval_required" value="1"
                        @checked($settings['correction_approval_required']) class="form-checkbox" />
                    {{ __('Require approval for every correction') }}
                </label>
            </div>

            <button type="submit" class="btn btn-primary">
                <x-icon name="check" class="icon-sm" />
                {{ __('Save attendance settings') }}
            </button>
        </form>
    </x-card>
</x-layouts.app>
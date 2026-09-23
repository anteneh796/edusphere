<x-layouts.app :title="__('Payroll')">

    <x-page-header :title="__('Payroll')" :description="__('Salary, allowances and banking details, visible only to payroll-enabled roles.')" />

    <x-card>
        <form method="GET" action="{{ route('hr.payroll.index') }}" class="flex gap-1" style="align-items:flex-end; padding: var(--space-3);">
            <div class="form-group" style="margin:0; flex:1;">
                <label class="form-label" for="q">{{ __('Search') }}</label>
                <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="{{ __('Name or employee ID…') }}" class="form-control" />
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
            @if (request('q'))
                <a href="{{ route('hr.payroll.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
            @endif
        </form>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Grade') }}</th>
                        <th>{{ __('Basic salary') }}</th>
                        <th>{{ __('Total earnings') }}</th>
                        <th>{{ __('Bank') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($profiles as $profile)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $profile->employee->full_name }}</div>
                                <div class="text-xs text-muted">{{ $profile->employee->employee_id }}</div>
                            </td>
                            <td class="text-sm text-muted">{{ $profile->employee->department?->name ?? '—' }}</td>
                            <td class="text-sm">{{ $profile->salary_grade ?? '—' }}</td>
                            <td class="text-sm">{{ $profile->basic_salary ? 'ETB '.number_format((float) $profile->basic_salary, 2) : '—' }}</td>
                            <td class="text-sm">{{ $profile->totalEarnings() ? 'ETB '.number_format($profile->totalEarnings(), 2) : '—' }}</td>
                            <td class="text-sm text-muted">{{ $profile->bank_name ?? '—' }}</td>
                            <td class="actions-cell">
                                <a href="{{ route('hr.payroll.edit', $profile->employee) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('Edit payroll') }}">
                                    <x-icon name="pencil" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="banknote" :title="__('No payroll profiles yet')" :message="__('Edit an employee\u2019s payroll page to add salary and banking details.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $profiles->links() }}</div>
    </x-card>

</x-layouts.app>
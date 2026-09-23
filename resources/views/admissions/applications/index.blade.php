<x-layouts.app :title="__('Admission applications')">
    <x-breadcrumb :items="[
        ['label' => __('Admissions'), 'url' => route('admissions.dashboard')],
        ['label' => __('Applications')],
    ]" />

    <x-page-header :title="__('Admission applications')" :description="__('Review and manage the full admissions pipeline.')">
        @can('create', \App\Domains\Admissions\Models\AdmissionApplication::class)
            <a href="{{ route('admissions.applications.create') }}" class="btn btn-primary">
                <x-icon name="user-plus" class="icon-sm" />
                {{ __('New application') }}
            </a>
        @endcan
    </x-page-header>

    <div class="grid grid-stats mt-4">
        <x-stat-card :label="__('Total applications')" :value="$kpis['total']" icon="file-text" color="primary" />
        <x-stat-card :label="__('Active candidates')" :value="$kpis['candidates']" icon="users" color="info" />
        <x-stat-card :label="__('Pending approval')" :value="$kpis['pending_approval']" icon="clock" color="warning" />
        <x-stat-card :label="__('Enrolled')" :value="$kpis['enrolled']" icon="graduation" color="success" />
    </div>

    <x-card>
        <form method="GET" action="{{ route('admissions.applications.index') }}" class="filter-grid">
            <x-input name="q" :label="__('Search')" :value="request('q')" :placeholder="__('Name or application number…')" wrapperClass="filter-q" />

            <div class="form-group">
                <label class="form-label" for="status-filter">{{ __('Status') }}</label>
                <select id="status-filter" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="grade-filter">{{ __('Grade') }}</label>
                <select id="grade-filter" name="grade_level_id" class="form-select">
                    <option value="">{{ __('All grades') }}</option>
                    @foreach ($gradeLevels as $grade)
                        <option value="{{ $grade->id }}" @selected(request('grade_level_id') === $grade->id)>{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-1" style="align-items:end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="search" class="icon-sm" />
                    {{ __('Filter') }}
                </button>
                @if (request('q') || request('status') || request('grade_level_id'))
                    <a href="{{ route('admissions.applications.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Applicant') }}</th>
                        <th>{{ __('Application no.') }}</th>
                        <th>{{ __('Grade / Intake') }}</th>
                        <th>{{ __('Primary guardian') }}</th>
                        <th>{{ __('Applied') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr>
                            <td>
                                <div class="avatar-cell">
                                    <x-avatar :initials="$application->initials()" size="sm" />
                                    <div style="min-width:0;">
                                        <a href="{{ route('admissions.applications.show', $application) }}" style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">
                                            {{ $application->full_name }}
                                        </a>
                                        <div class="text-xs text-muted">{{ ucfirst($application->gender ?? '—') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="code-chip">{{ $application->application_number }}</span>
                            </td>
                            <td class="text-sm">
                                <div>{{ $application->gradeLevel->name ?? '—' }}</div>
                                <div class="text-xs text-muted">{{ $application->intakeYear->name ?? __('Current year') }}</div>
                            </td>
                            <td class="text-sm">
                                @if ($application->primaryGuardian)
                                    <div>{{ $application->primaryGuardian->full_name }}</div>
                                    <div class="text-xs text-muted">{{ $application->primaryGuardian->phone ?? '—' }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-sm">{{ optional($application->applied_at)->format('M j, Y') }}</td>
                            <td>
                                <x-badge :color="$application->statusBadgeColor()">
                                    {{ $application->statusLabel() }}
                                </x-badge>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('admissions.applications.show', $application) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('View')">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                                @can('update', $application)
                                    <a href="{{ route('admissions.applications.edit', $application) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('Edit')">
                                        <x-icon name="pencil" class="icon-sm" />
                                    </a>
                                @endcan
                                @can('delete', $application)
                                    <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger"
                                        :title="__('Archive')"
                                        @click="$store.confirm.ask({
                                            title: @js(__('Archive application?')),
                                            message: @js(__('Archive').' '.$application->full_name.' ('.$application->application_number.'). '.__('This cannot be undone.')),
                                            action: @js(route('admissions.applications.destroy', $application)),
                                            method: 'DELETE',
                                            confirmText: @js(__('Archive'))
                                        })">
                                        <x-icon name="trash" class="icon-sm" />
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="clipboard" :title="__('No applications found')" :message="__('Try adjusting your filters or record a new application.')">
                                    @can('create', \App\Domains\Admissions\Models\AdmissionApplication::class)
                                        <a href="{{ route('admissions.applications.create') }}" class="btn btn-primary btn-sm">{{ __('New application') }}</a>
                                    @endcan
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $applications->links() }}
        </div>
    </x-card>
</x-layouts.app>
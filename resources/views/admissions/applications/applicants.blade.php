<x-layouts.app :title="__('Applicants')">
    <x-breadcrumb :items="[
        ['label' => __('Admissions'), 'url' => route('admissions.dashboard')],
        ['label' => __('Applicants')],
    ]" />

    <x-page-header :title="__('Applicants directory')" :description="__('Active candidates searching for a place at school.')">
        @can('create', \App\Domains\Admissions\Models\AdmissionApplication::class)
            <a href="{{ route('admissions.applications.create') }}" class="btn btn-primary">
                <x-icon name="user-plus" class="icon-sm" />
                {{ __('New application') }}
            </a>
        @endcan
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('admissions.applicants.index') }}" class="filter-grid">
            <x-input name="q" :label="__('Search')" :value="request('q')" :placeholder="__('Name or application number…')" wrapperClass="filter-q" />

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
                @if (request('q') || request('grade_level_id'))
                    <a href="{{ route('admissions.applicants.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
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
                        <th>{{ __('Age') }}</th>
                        <th>{{ __('Stage') }}</th>
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
                            <td class="text-sm">{{ $application->date_of_birth ? $application->date_of_birth->age.' '.__('yrs') : '—' }}</td>
                            <td>
                                <x-badge :color="$application->statusBadgeColor()">
                                    {{ $application->statusLabel() }}
                                </x-badge>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('admissions.applications.show', $application) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('View')">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="users" :title="__('No candidates found')" :message="__('There are no active candidates matching the current filters.')" />
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
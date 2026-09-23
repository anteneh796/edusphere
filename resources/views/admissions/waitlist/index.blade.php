<x-layouts.app :title="__('Waiting list')">
    <x-breadcrumb :items="[
        ['label' => __('Admissions'), 'url' => route('admissions.dashboard')],
        ['label' => __('Waiting list')],
    ]" />

    <x-page-header :title="__('Waiting list')" :description="__('Candidates held back because their grade is full.')" />

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Position') }}</th>
                        <th>{{ __('Applicant') }}</th>
                        <th>{{ __('Application no.') }}</th>
                        <th>{{ __('Grade / Intake') }}</th>
                        <th>{{ __('Waitlisted') }}</th>
                        <th>{{ __('Notes') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr>
                            <td>
                                <span class="badge badge-neutral" style="min-width: 34px; justify-content:center;">#{{ $application->waitlist_position }}</span>
                            </td>
                            <td>
                                <div class="avatar-cell">
                                    <x-avatar :initials="$application->initials()" size="sm" />
                                    <div style="min-width:0;">
                                        <a href="{{ route('admissions.applications.show', $application) }}" style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">
                                            {{ $application->full_name }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td><span class="code-chip">{{ $application->application_number }}</span></td>
                            <td class="text-sm">
                                <div>{{ $application->gradeLevel->name ?? '—' }}</div>
                                <div class="text-xs text-muted">{{ $application->intakeYear->name ?? __('Current year') }}</div>
                            </td>
                            <td class="text-sm">{{ optional($application->waitlisted_at)->format('M j, Y') }}</td>
                            <td class="text-sm text-muted">{{ \Illuminate\Support\Str::limit($application->decision_comment, 60) ?? '—' }}</td>
                            <td class="actions-cell">
                                @can('promote', $application)
                                    <form method="POST" action="{{ route('admissions.applications.promote', $application) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('{{ __('Promote :name to approved?', ['name' => $application->full_name]) }}')">
                                            {{ __('Promote') }}
                                        </button>
                                    </form>
                                @endcan
                                @can('decide', $application)
                                    <form method="POST" action="{{ route('admissions.applications.decide', $application) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="reject" />
                                        <button type="submit" class="btn btn-ghost btn-sm" onclick="return confirm('{{ __('Reject :name?', ['name' => $application->full_name]) }}')">
                                            {{ __('Reject') }}
                                        </button>
                                    </form>
                                @endcan
                                <a href="{{ route('admissions.applications.show', $application) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('View')">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="users" :title="__('Waiting list is empty')" :message="__('No candidates are currently waitlisted.')" />
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
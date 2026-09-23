<x-layouts.app :title="__('Pending approvals')">
    <x-breadcrumb :items="[
        ['label' => __('Admissions'), 'url' => route('admissions.dashboard')],
        ['label' => __('Approvals')],
    ]" />

    <x-page-header :title="__('Admission approvals')" :description="__('Applications awaiting a decision from leadership.')" />

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Applicant') }}</th>
                        <th>{{ __('Application no.') }}</th>
                        <th>{{ __('Grade / Intake') }}</th>
                        <th>{{ __('Applied') }}</th>
                        <th>{{ __('Notes') }}</th>
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
                            <td><span class="code-chip">{{ $application->application_number }}</span></td>
                            <td class="text-sm">
                                <div>{{ $application->gradeLevel->name ?? '—' }}</div>
                                <div class="text-xs text-muted">{{ $application->intakeYear->name ?? __('Current year') }}</div>
                            </td>
                            <td class="text-sm">{{ optional($application->applied_at)->format('M j, Y') }}</td>
                            <td class="text-sm text-muted">{{ \Illuminate\Support\Str::limit($application->decision_comment, 60) ?? '—' }}</td>
                            <td class="actions-cell">
                                @can('decide', $application)
                                    <x-modal title="{{ __('Admission decision') }}">
                                        <x-slot:trigger>
                                            <button type="button" class="btn btn-primary btn-sm">
                                                <x-icon name="check" class="icon-sm" />
                                                {{ __('Decide') }}
                                            </button>
                                        </x-slot:trigger>
                                        <form method="POST" action="{{ route('admissions.applications.decide', $application) }}">
                                            @csrf
                                            <div class="form-group">
                                                <label class="form-label" for="decide-{{ $application->id }}">{{ __('Decision') }} <span class="required">*</span></label>
                                                <select id="decide-{{ $application->id }}" name="decision" class="form-select" required>
                                                    <option value="approve">{{ __('Approve') }}</option>
                                                    <option value="reject">{{ __('Reject') }}</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="comment-{{ $application->id }}">{{ __('Comment') }}</label>
                                                <textarea id="comment-{{ $application->id }}" name="comment" class="form-control" rows="3"></textarea>
                                            </div>
                                            <label class="form-check">
                                                <input type="checkbox" name="force" value="1" />
                                                <span>{{ __('Override grade capacity') }}</span>
                                            </label>
                                            <div class="card-footer">
                                                <button type="submit" class="btn btn-primary">{{ __('Record decision') }}</button>
                                            </div>
                                        </form>
                                    </x-modal>
                                @endcan
                                <a href="{{ route('admissions.applications.show', $application) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('View')">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="check-circle" :title="__('Nothing awaiting approval')" :message="__('All applications have been reviewed and decided.')" />
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
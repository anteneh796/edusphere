<x-layouts.app :title="__('Admission assessments')">
    <x-breadcrumb :items="[
        ['label' => __('Admissions'), 'url' => route('admissions.dashboard')],
        ['label' => __('Assessments')],
    ]" />

    <x-page-header :title="__('Admission assessments')" :description="__('Scheduled aptitude interviews and placement tests.')" />

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Applicant') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Scheduled') }}</th>
                        <th>{{ __('Location') }}</th>
                        <th>{{ __('Result') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assessments as $assessment)
                        <tr>
                            <td>
                                <a href="{{ route('admissions.applications.show', $assessment->application) }}" style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">
                                    {{ $assessment->application?->full_name }}
                                </a>
                                <div class="text-xs text-muted">
                                    <span class="code-chip">{{ $assessment->application?->application_number }}</span>
                                    {{ $assessment->application?->gradeLevel?->name }}
                                </div>
                            </td>
                            <td class="text-sm">{{ $assessment->typeLabel() }}</td>
                            <td class="text-sm">{{ $assessment->scheduled_at?->format('M j, Y g:i A') }}</td>
                            <td class="text-sm text-muted">{{ $assessment->location ?? '—' }}</td>
                            <td class="text-sm">
                                @if ($assessment->score !== null)
                                    {{ $assessment->score }}/100
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <x-badge :color="$assessment->statusBadgeColor()">{{ $assessment->statusLabel() }}</x-badge>
                            </td>
                            <td class="actions-cell">
                                @if ($assessment->isOpen())
                                    <a href="{{ route('admissions.applications.show', $assessment->application) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('Record result')">
                                        <x-icon name="pencil" class="icon-sm" />
                                    </a>
                                @endif
                                <a href="{{ route('admissions.applications.show', $assessment->application) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('View')">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="clipboard-check" :title="__('No assessments scheduled')" :message="__('Schedule assessments from an application in review.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $assessments->links() }}
        </div>
    </x-card>
</x-layouts.app>
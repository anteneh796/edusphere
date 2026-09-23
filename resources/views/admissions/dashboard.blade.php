<x-layouts.app :title="__('Admissions')">
    <x-breadcrumb :items="[['label' => __('Admissions')]]" />

    <x-page-header :title="__('Admissions dashboard')" :description="__('Track the applicant pipeline, grade capacity and pending decisions.')">
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
        <x-stat-card :label="__('Waiting list')" :value="$kpis['waitlisted']" icon="users" color="accent" />
        <x-stat-card :label="__('Unhandled inquiries')" :value="$kpis['unhandled_inquiries']" icon="bell" color="danger" />
    </div>

    <div class="dashboard-grid mt-4">
        <x-card :title="__('Pipeline')" subtitle="{{ __('Applications per stage') }}" style="grid-column: span 2;">
            <div class="flex gap-1 flex-wrap">
                @foreach ($pipeline as $stage)
                    <a href="{{ route('admissions.applications.index', ['status' => $stage['key']]) }}"
                       style="flex: 1 1 120px; min-width: 120px; padding: var(--space-2); border: 1px solid var(--color-border); border-radius: var(--radius); text-decoration: none; color: var(--color-text);">
                        <div style="font-size: var(--text-xl); font-weight: var(--weight-bold);">{{ $stage['count'] }}</div>
                        <div class="text-xs text-muted">{{ $stage['label'] }}</div>
                    </a>
                @endforeach
            </div>
        </x-card>

        <x-card title="{{ __('Recent applications') }}">
            <x-slot:actions>
                <a href="{{ route('admissions.applications.index') }}" class="btn btn-ghost btn-sm">{{ __('View all') }}</a>
            </x-slot:actions>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        @forelse ($recentApplications as $application)
                            <tr>
                                <td>
                                    <div class="avatar-cell">
                                        <x-avatar :initials="$application->initials()" size="sm" />
                                        <div style="min-width:0;">
                                            <a href="{{ route('admissions.applications.show', $application) }}" style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">
                                                {{ $application->full_name }}
                                            </a>
                                            <div class="text-xs text-muted">
                                                <span class="code-chip">{{ $application->application_number }}</span>
                                                {{ $application->gradeLevel->name ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <x-badge :color="$application->statusBadgeColor()">{{ $application->statusLabel() }}</x-badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td><x-empty-state icon="file-text" :title="__('No applications yet')" :message="__('All new applications will appear here.')" /></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card :title="__('Capacity alerts')" subtitle="{{ __('Grades at or above 75% capacity') }}" style="grid-column: span 2;">
            @if ($capacityAlerts->isNotEmpty())
                @foreach ($capacityAlerts as $alert)
                    <div class="list-row">
                        <div style="min-width:0; flex:1;">
                            <div class="flex" style="justify-content:space-between; align-items:center;">
                                <span style="font-weight: var(--weight-semibold);">{{ $alert['grade']->name }}</span>
                                <span class="text-sm text-muted">{{ $alert['taken'] }} / {{ $alert['capacity'] }}</span>
                            </div>
                            <div class="chart-bar-track">
                                <div class="chart-bar-fill {{ $alert['overflow'] ? 'is-overflow' : '' }}" style="width: {{ min($alert['utilization'], 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <x-empty-state icon="check-circle" :title="__('All grades have room')" :message="__('No grade has reached the capacity alert threshold.')" />
            @endif
        </x-card>

        <x-card :title="__('Admissions inquiries')" subtitle="{{ __('Recent website inquiries') }}">
            @forelse ($inquiries as $inquiry)
                <div class="list-row">
                    <div style="min-width:0; flex:1;">
                        <div style="font-weight: var(--weight-semibold);">{{ $inquiry->name }}</div>
                        <div class="text-xs text-muted">
                            {{ $inquiry->phone ?? '' }} {{ $inquiry->phone && $inquiry->email ? '·' : '' }} {{ $inquiry->email ?? '' }}
                            @if ($inquiry->created_at) · {{ $inquiry->created_at->format('M j, Y') }} @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admissions.inquiries.handle', $inquiry) }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm btn-icon" :title="__('Convert to application')">
                            <x-icon name="check" class="icon-sm" />
                        </button>
                    </form>
                </div>
            @empty
                <x-empty-state icon="bell" :title="__('No inquiries')" :message="__('Admissions inquiries from the public website appear here.')" />
            @endforelse
        </x-card>
    </div>
</x-layouts.app>
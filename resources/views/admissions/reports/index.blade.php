<x-layouts.app :title="__('Admission reports')">
    <x-breadcrumb :items="[
        ['label' => __('Admissions'), 'url' => route('admissions.dashboard')],
        ['label' => __('Reports')],
    ]" />

    <x-page-header :title="__('Admission reports')" :description="__('Conversion funnel, intake trends and applicant demographics.')" />

    <div class="grid grid-stats mt-4">
        <x-stat-card :label="__('Total applications')" :value="$kpis['total']" icon="file-text" color="primary" />
        <x-stat-card :label="__('Enrolled')" :value="$kpis['enrolled']" icon="graduation" color="success" />
        <x-stat-card :label="__('Conversion rate')" :value="$kpis['conversion_rate'].'%'" icon="trending-up" color="accent" />
        <x-stat-card :label="__('Avg. days to decision')" :value="$averageDays !== null ? $averageDays : '—'" icon="clock" color="info" />
    </div>

    <div class="dashboard-grid mt-4">
        <x-card title="{{ __('Conversion funnel') }}" subtitle="{{ __('Share of all applications reaching each stage') }}" style="grid-column: span 2;">
            @foreach ($funnel['stages'] as $key => $stage)
                <div class="list-row">
                    <div style="min-width:0; flex:1;">
                        <div class="flex" style="justify-content:space-between; align-items:center;">
                            <span style="font-weight: var(--weight-semibold);">{{ \Illuminate\Support\Str::headline($key) }}</span>
                            <span class="text-sm text-muted">{{ $stage['count'] }} · {{ $stage['rate'] }}%</span>
                        </div>
                        <div class="chart-bar-track">
                            <div class="chart-bar-fill" style="width: {{ min($stage['rate'], 100) }}%;"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </x-card>

        <x-card title="{{ __('Applications by grade') }}" subtitle="{{ __('All time by requested grade') }}">
            @forelse ($byGrade as $row)
                <div class="list-row">
                    <div style="min-width:0; flex:1;">
                        <div class="flex" style="justify-content:space-between; align-items:center;">
                            <span class="text-sm" style="font-weight: var(--weight-semibold);">{{ $row['name'] }}</span>
                            <span class="text-sm text-muted">{{ $row['count'] }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <x-empty-state icon="bar-chart" :title="__('No data yet')" :message="__('Applications will populate these charts.')" />
            @endforelse
        </x-card>

        <x-card title="{{ __('Monthly applications') }}" subtitle="{{ __('Last six months') }}" style="grid-column: span 2;">
            <div class="flex" style="gap: var(--space-2); align-items:flex-end; min-height: 140px;">
                @foreach ($monthly as $month)
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap: 6px;">
                        <span class="text-xs text-muted">{{ $month['count'] }}</span>
                        <div class="chart-bar-fill" style="height: {{ max($month['count'] * 12, 4) }}px; width: 32px;"></div>
                        <span class="text-xs text-muted">{{ $month['month'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card title="{{ __('Gender split') }}">
            @forelse ($genderSpread as $row)
                <div class="list-row">
                    <div style="min-width:0; flex:1;">
                        <div class="flex" style="justify-content:space-between; align-items:center;">
                            <span class="text-sm">{{ $row['label'] }}</span>
                            <span class="text-sm text-muted">{{ $row['count'] }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <span class="text-sm text-muted">—</span>
            @endforelse
        </x-card>

        <x-card title="{{ __('Application sources') }}">
            @foreach ($sourceSpread as $row)
                <div class="list-row">
                    <div style="min-width:0; flex:1;">
                        <div class="flex" style="justify-content:space-between; align-items:center;">
                            <span class="text-sm">{{ $row['label'] }}</span>
                            <span class="text-sm text-muted">{{ $row['count'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </x-card>
    </div>
</x-layouts.app>
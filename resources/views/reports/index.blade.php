<x-layouts.app :title="__('Reports & Analytics')">

    <x-page-header :title="__('Reports & Analytics')" :description="__('Executive overview of academics, operations and communications.')
        .($currentYear ? ' · '.$currentYear->name : '')" />

    <div class="grid grid-stats">
        <x-stat-card :label="__('Enrolled students')" :value="$kpis['students']" icon="graduation" color="primary" />
        <x-stat-card :label="__('Teaching staff')" :value="$kpis['teachers']" icon="users-large" color="info" />
        <x-stat-card :label="__('Classes')" :value="$kpis['classes']" icon="book-open" color="success" />
        <x-stat-card :label="__('Attendance rate')" :value="$kpis['attendance_rate'] !== null ? $kpis['attendance_rate'].'%' : '—'" icon="clipboard-check" color="warning" />
        <x-stat-card :label="__('Pending approvals')" :value="$kpis['pending_approvals']" icon="file-text" color="accent" />
        <x-stat-card :label="__('Fee collection')" :value="$kpis['fee_collection'] === null ? '—' : $kpis['fee_collection'].'%'" icon="banknote" color="danger" hint="Finance module not installed" />
    </div>

    <div class="grid" style="grid-template-columns: 2fr 1fr; gap: var(--space-3);">
        <x-card :title="__('Enrollment by grade')" :subtitle="__('Kindergarten – Grade 8')">
            @if ($enrollment->isEmpty())
                <x-empty-state icon="graduation" :title="__('No grade levels')" :message="__('Configure grades to see enrollment distribution.')" />
            @else
                @php($max = max($enrollment->max('count'), 1))
                <div class="flex flex-col" style="gap: var(--space-2);">
                    @foreach ($enrollment as $row)
                        <div>
                            <div class="flex" style="justify-content: space-between; font-size: var(--text-sm); margin-bottom: 2px;">
                                <span style="font-weight: var(--weight-medium);">{{ $row['name'] }} <span class="text-xs text-muted">({{ $row['code'] }})</span></span>
                                <span class="text-muted">{{ $row['count'] }}</span>
                            </div>
                            <div class="chart-bar-track">
                                <div class="chart-bar-fill"
                                    style="width: {{ round(($row['count'] / $max) * 100) }}%;">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <div class="flex flex-col" style="gap: var(--space-3);">
            <x-card :title="__('Staff by type')">
                @if ($staffByType->sum('count') === 0)
                    <p class="text-sm text-muted">{{ __('No staff records yet.') }}</p>
                @else
                    <dl class="detail-list">
                        @foreach ($staffByType as $row)
                            <dt>{{ $row['label'] }}</dt>
                            <dd>{{ $row['count'] }}</dd>
                        @endforeach
                    </dl>
                @endif
            </x-card>

            <x-card :title="__('Approval pipeline')">
                <dl class="detail-list">
                    <dt>{{ __('Pending') }}</dt>
                    <dd>{{ $approvalPipeline['pending'] ?? 0 }}</dd>
                    <dt>{{ __('Approved') }}</dt>
                    <dd>{{ $approvalPipeline['approved'] ?? 0 }}</dd>
                    <dt>{{ __('Denied') }}</dt>
                    <dd>{{ $approvalPipeline['denied'] ?? 0 }}</dd>
                </dl>
            </x-card>
        </div>
    </div>

    <x-card :title="__('Website inquiries · last 6 months')">
        @if ($inquiries->isEmpty())
            <x-empty-state icon="mail" :title="__('No inquiries')" :message="__('Inquiries submitted through the website will appear here.')" />
        @else
            @php($max = max($inquiries->max('count'), 1))
            <div class="flex" style="gap: var(--space-3); align-items:flex-end; min-height: 180px;">
                @foreach ($inquiries as $row)
                    <div class="flex flex-col" style="flex:1; align-items:center; gap: 4px;">
                        <span class="text-sm" style="font-weight: var(--weight-semibold);">{{ $row['count'] }}</span>
                        <div class="chart-bar-fill" style="height: {{ max(round(($row['count'] / $max) * 120), 4) }}px; width: 32px;"></div>
                        <span class="text-xs text-muted">{{ $row['month'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

</x-layouts.app>
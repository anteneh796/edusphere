@php
    $reportTabs = [
        ['label' => __('Daily'), 'route' => 'attendance.reports.daily'],
        ['label' => __('Student'), 'route' => 'attendance.reports.student'],
        ['label' => __('Monthly'), 'route' => 'attendance.reports.monthly'],
        ['label' => __('Class'), 'route' => 'attendance.reports.class'],
        ['label' => __('Grade'), 'route' => 'attendance.reports.grade'],
        ['label' => __('Term'), 'route' => 'attendance.reports.term'],
        ['label' => __('Late'), 'route' => 'attendance.reports.late'],
        ['label' => __('Absence & lateness'), 'route' => 'attendance.reports.status'],
        ['label' => __('Trend'), 'route' => 'attendance.reports.trend'],
        ['label' => __('Completion'), 'route' => 'attendance.reports.completion'],
    ];
    $activeTab = $activeTab ?? Route::currentRouteName();
@endphp

<div class="flex" style="gap: var(--space-1); flex-wrap: wrap; margin-bottom: var(--space-4);">
    @foreach ($reportTabs as $tab)
        <a href="{{ route($tab['route']) }}" class="btn {{ $activeTab === $tab['route'] ? 'btn-primary' : 'btn-ghost' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
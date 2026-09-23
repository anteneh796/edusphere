@php
    $portalTabs = [
        ['label' => __('Overview'), 'route' => 'attendance.dashboard', 'icon' => 'dashboard', 'permissions' => ['attendance.view']],
        ['label' => __('Sessions'), 'route' => 'attendance.index', 'icon' => 'clipboard-check', 'permissions' => ['attendance.view']],
        ['label' => __('Reports'), 'route' => 'attendance.reports.daily', 'icon' => 'bar-chart', 'permissions' => ['attendance.view']],
        ['label' => __('Corrections'), 'route' => 'attendance.corrections', 'icon' => 'refresh', 'permissions' => ['attendance.approve']],
        ['label' => __('Alerts'), 'route' => 'attendance.alerts', 'icon' => 'alert-triangle', 'permissions' => ['attendance.view']],
        ['label' => __('Settings'), 'route' => 'attendance.settings', 'icon' => 'settings', 'permissions' => ['attendance.configure']],
    ];

    $activeTab = $activeTab ?? Route::currentRouteName();
    $user = auth()->user();
@endphp

<div class="flex" style="gap: var(--space-1); flex-wrap: wrap; margin-bottom: var(--space-4);">
    @foreach ($portalTabs as $tab)
        @if ($user?->hasAnyPermission($tab['permissions']))
            <a href="{{ route($tab['route']) }}"
                class="btn {{ $activeTab === $tab['route'] ? 'btn-primary' : 'btn-ghost' }}"
                style="gap: var(--space-1);">
                <x-icon :name="$tab['icon']" class="icon-sm" />
                {{ $tab['label'] }}
            </a>
        @endif
    @endforeach
</div>
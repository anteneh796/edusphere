<x-layouts.app :title="__('Student Information System')">
    <x-breadcrumb :items="[['label' => __('Students')], ['label' => __('Dashboard')]]" />

    <x-page-header :title="__('Student Information System')" :description="__('A complete KG through Grade 8 view of the student lifecycle for the current academic year.')">
        <div class="flex gap-1 flex-wrap">
            <a href="{{ route('students.index') }}" class="btn btn-secondary"><x-icon name="users" class="icon-sm" /> {{ __('Student records') }}</a>
            @can('create', AppDomainsStudentsModelsStudent::class)
                <a href="{{ route('students.create') }}" class="btn btn-primary"><x-icon name="user-plus" class="icon-sm" /> {{ __('Register student') }}</a>
            @endcan
        </div>
    </x-page-header>

    <div class="stat-grid" style="grid-template-columns:repeat(4,minmax(0,1fr));">
        <x-card>
            <div class="text-xs text-muted">{{ __('Current students') }}</div>
            <div class="stat-value">{{ number_format($totalStudents) }}</div>
            <div class="text-xs text-muted">{{ $year->name }}</div>
        </x-card>
        <x-card>
            <div class="text-xs text-muted">{{ __('New admissions') }}</div>
            <div class="stat-value">{{ number_format($newAdmissions) }}</div>
            <div class="text-xs text-muted">{{ __('Created this month') }}</div>
        </x-card>
        <x-card>
            <div class="text-xs text-muted">{{ __('Transfers') }}</div>
            <div class="stat-value">{{ number_format($transfers) }}</div>
            <div class="text-xs text-muted">{{ __('This academic year') }}</div>
        </x-card>
        <x-card>
            <div class="text-xs text-muted">{{ __('Grade 8 candidates') }}</div>
            <div class="stat-value">{{ number_format($grade8Candidates) }}</div>
            <div class="text-xs text-muted">{{ __('Potential graduates') }}</div>
        </x-card>
    </div>

    <x-card title="{{ __('Students by grade') }}" class="mt-3">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>{{ __('Grade') }}</th><th>{{ __('Students') }}</th><th>{{ __('Share') }}</th></tr></thead>
                <tbody>
                @foreach($byGrade as $grade)
                    @php($share = $totalStudents > 0 ? round(($grade->current_students_count / $totalStudents) * 100, 1) : 0)
                    <tr>
                        <td><strong>{{ $grade->name }}</strong></td>
                        <td>{{ number_format($grade->current_students_count) }}</td>
                        <td style="min-width:220px;">
                            <div class="flex gap-1" style="align-items:center;">
                                <div style="height:8px;flex:1;background:var(--color-surface-muted);border-radius:999px;overflow:hidden;"><div style="height:100%;width:{{ $share }}%;background:var(--color-primary);"></div></div>
                                <span class="text-xs text-muted">{{ $share }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-card>

    <div class="grid mt-3" style="grid-template-columns:repeat(3,minmax(0,1fr));gap:var(--space-3);">
        <x-card title="{{ __('Student lifecycle') }}">
            <div class="flex flex-col" style="gap:var(--space-2);">
                <a class="btn btn-secondary" href="{{ route('students.index', ['status' => 'new']) }}">{{ __('New students') }}</a>
                <a class="btn btn-secondary" href="{{ route('students.index', ['transfer' => 1]) }}">{{ __('Transferred / withdrawn') }}</a>
                @can('promote', AppDomainsStudentsModelsStudent::class)<a class="btn btn-secondary" href="{{ route('students.promote') }}">{{ __('Promotion center') }}</a>@endcan
            </div>
        </x-card>
        <x-card title="{{ __('Identity & records') }}">
            <div class="flex flex-col" style="gap:var(--space-2);">
                <a class="btn btn-secondary" href="{{ route('students.roster') }}">{{ __('Class rosters') }}</a>
                @can('export', AppDomainsStudentsModelsStudent::class)<a class="btn btn-secondary" href="{{ route('students.export') }}">{{ __('Export student register') }}</a>@endcan
            </div>
        </x-card>
        <x-card title="{{ __('Grade 8') }}">
            <p class="text-sm text-muted">{{ __('Grade 8 students remain in the SIS permanently. Promotion marks them as graduated rather than creating a Grade 9 enrollment.') }}</p>
        </x-card>
    </div>
</x-layouts.app>
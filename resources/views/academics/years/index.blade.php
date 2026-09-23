<x-layouts.app :title="__('Academic years')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Academic years')],
    ]" />

    <x-page-header :title="__('Academic years')" :description="__('School calendar years. The current year drives enrollments and classes.')">
        @can('create', App\Domains\Academics\Models\AcademicYear::class)
            <a href="{{ route('academics.years.create') }}" class="btn btn-primary btn-sm">
                <x-icon name="plus" class="icon-sm" />
                {{ __('New academic year') }}
            </a>
        @endcan
    </x-page-header>

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Year') }}</th>
                        <th>{{ __('Period') }}</th>
                        <th class="text-right">{{ __('Classes') }}</th>
                        <th class="text-right">{{ __('Enrollments') }}</th>
                        <th class="text-right">{{ __('Terms') }}</th>
                        <th>{{ __('Status') }}</th>
                        @if (auth()->user()->hasAnyPermission(['academics.edit', 'academics.delete']))
                            <th class="text-right">{{ __('Actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($years as $row)
                        @php($year = $row['year'])
                        <tr>
                            <td>
                                <span style="font-weight: var(--weight-semibold);">{{ $year->name }}</span>
                            </td>
                            <td class="text-sm text-muted">
                                {{ $year->start_date->format('M j, Y') }} – {{ $year->end_date->format('M j, Y') }}
                            </td>
                            <td class="text-right text-sm"><strong>{{ $row['classes'] }}</strong></td>
                            <td class="text-right text-sm"><strong>{{ $row['enrollments'] }}</strong></td>
                            <td class="text-right text-sm"><strong>{{ $row['terms'] }}</strong></td>
                            <td>
                                @if ($year->is_current)
                                    <x-badge :label="__('Current')" color="success" dot />
                                @else
                                    <x-badge :label="__('Past')" color="neutral" />
                                @endif
                            </td>
                            @if (auth()->user()->hasAnyPermission(['academics.edit', 'academics.delete']))
                                <td class="text-right">
                                    <div class="flex gap-1 justify-end">
                                        @can('update', $year)
                                            @if (! $year->is_current)
                                                <form method="POST" action="{{ route('academics.years.activate', $year) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary btn-sm">{{ __('Make current') }}</button>
                                                </form>
                                            @endif
                                            <a href="{{ route('academics.years.edit', $year) }}" class="btn btn-icon btn-secondary btn-sm" title="{{ __('Edit') }}">
                                                <x-icon name="pencil" class="icon-sm" />
                                            </a>
                                        @endcan
                                        @can('delete', $year)
                                            <form method="POST" action="{{ route('academics.years.destroy', $year) }}" onsubmit="return confirm('{{ __('Delete this academic year?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon btn-secondary btn-sm" title="{{ __('Delete') }}">
                                                    <x-icon name="trash" class="icon-sm" />
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="calendar" :title="__('No academic years')" :message="__('Create the current academic year to get started.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>
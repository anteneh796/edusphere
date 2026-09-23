<x-layouts.app :title="__('Grade capacity')">
    <x-breadcrumb :items="[
        ['label' => __('Admissions'), 'url' => route('admissions.dashboard')],
        ['label' => __('Grade capacity')],
    ]" />

    <x-page-header :title="__('Grade capacity')" :description="__('Control how many candidates can be admitted per grade and intake year.')" />

    <x-card :title="__('Capacity plans')" :subtitle="$year->name ?? __('Current academic year')">
        @can('update', \App\Domains\Admissions\Models\GradeCapacity::class)
            <form method="POST" action="{{ route('admissions.capacity.update') }}" novalidate>
                @csrf
                @method('PUT')

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Grade') }}</th>
                                <th>{{ __('Capacity') }}</th>
                                <th>{{ __('Taken') }}</th>
                                <th>{{ __('Utilization') }}</th>
                                <th>{{ __('Allow override') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $index => $row)
                                <tr>
                                    <input type="hidden" name="capacities[{{ $index }}][grade_level_id]" value="{{ $row['grade']->id }}" />
                                    <td style="font-weight: var(--weight-semibold);">{{ $row['grade']->name }}</td>
                                    <td style="max-width: 160px;">
                                        <input type="number" name="capacities[{{ $index }}][capacity]" class="form-control" min="0" max="9999" value="{{ old('capacities.'.$index.'.capacity', $row['capacity']) }}" required />
                                    </td>
                                    <td class="text-sm">{{ $row['taken'] }}</td>
                                    <td style="min-width: 200px;">
                                        <div class="chart-bar-track">
                                            <div class="chart-bar-fill {{ $row['utilization'] >= 100 ? 'is-overflow' : '' }}"
                                                 style="width: {{ min($row['utilization'], 100) }}%; {{ $row['utilization'] >= 100 ? 'background: var(--color-danger);' : ($row['utilization'] >= 75 ? 'background: var(--color-warning);' : '') }}">
                                            </div>
                                        </div>
                                        <div class="text-xs text-muted" style="margin-top: 4px;">{{ $row['utilization'] }}%</div>
                                    </td>
                                    <td>
                                        <label class="form-check">
                                            <input type="checkbox" name="capacities[{{ $index }}][allow_override]" value="1"
                                                @checked($row['record']?->allow_override) />
                                            <span>{{ __('Allow approving past capacity') }}</span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Save capacity plans') }}
                    </button>
                </div>
            </form>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Grade') }}</th>
                            <th>{{ __('Capacity') }}</th>
                            <th>{{ __('Taken') }}</th>
                            <th>{{ __('Utilization') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td style="font-weight: var(--weight-semibold);">{{ $row['grade']->name }}</td>
                                <td class="text-sm">{{ $row['capacity'] }}</td>
                                <td class="text-sm">{{ $row['taken'] }}</td>
                                <td style="min-width: 200px;">
                                    <div class="chart-bar-track">
                                        <div class="chart-bar-fill" style="width: {{ min($row['utilization'], 100) }}%;"></div>
                                    </div>
                                    <div class="text-xs text-muted" style="margin-top: 4px;">{{ $row['utilization'] }}%</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endcan
    </x-card>
</x-layouts.app>
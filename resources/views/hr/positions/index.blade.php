<x-layouts.app :title="__('Positions')">

    <x-page-header :title="__('Positions')" :description="__('Job titles and roles across the school, grouped by category.')">
        @if (auth()->user()->hasPermission('hr.create'))
            <a href="{{ route('hr.positions.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                {{ __('Add position') }}
            </a>
        @endif
    </x-page-header>

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Position') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Employees') }}</th>
                        <th width="40%">{{ __('Description') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($positions as $position)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $position->name }}</div>
                                <div class="text-xs text-muted">{{ $position->slug }}</div>
                            </td>
                            <td>
                                <x-badge color="neutral">{{ \App\Domains\HumanResources\Controllers\PositionController::CATEGORIES[$position->category] }}</x-badge>
                            </td>
                            <td class="text-sm text-muted">{{ $position->department?->name ?? '—' }}</td>
                            <td class="text-sm">{{ $position->employees_count }}</td>
                            <td class="text-sm text-muted">{{ \Illuminate\Support\Str::limit($position->description, 80) ?? '—' }}</td>
                            <td class="actions-cell">
                                @if (auth()->user()->hasPermission('hr.edit'))
                                    <a href="{{ route('hr.positions.edit', $position) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('Edit') }}">
                                        <x-icon name="pencil" class="icon-sm" />
                                    </a>
                                @endif
                                @if (auth()->user()->hasPermission('hr.delete'))
                                    <form method="POST" action="{{ route('hr.positions.destroy', $position) }}" class="inline" onsubmit="return confirm('{{ __('Delete this position?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm btn-icon" style="color:var(--color-danger);" title="{{ __('Delete') }}">
                                            <x-icon name="trash" class="icon-sm" />
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="briefcase" :title="__('No positions yet')" :message="__('Positions let you categorize staff by job and career path.')">
                                    <a href="{{ route('hr.positions.create') }}" class="btn btn-primary btn-sm">{{ __('Add position') }}</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $positions->links() }}</div>
    </x-card>

</x-layouts.app>
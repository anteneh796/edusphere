<x-layouts.app :title="__('Departments')">

    <x-page-header :title="__('Departments')" :description="__('Organizational units and their staffing.')">
        @if (auth()->user()->hasPermission('hr.create'))
            <a href="{{ route('hr.departments.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                {{ __('Add department') }}
            </a>
        @endif
    </x-page-header>

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Staff') }}</th>
                        <th>{{ __('Positions') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $department->name }}</div>
                                <div class="text-xs text-muted">{{ $department->code }}</div>
                            </td>
                            <td class="text-sm"><x-badge color="neutral">{{ $department->code }}</x-badge></td>
                            <td class="text-sm">{{ $department->employees_count }}</td>
                            <td class="text-sm">{{ $department->positions_count }}</td>
                            <td class="text-sm text-muted">{{ \Illuminate\Support\Str::limit($department->description, 60) ?? '—' }}</td>
                            <td class="actions-cell">
                                @if (auth()->user()->hasPermission('hr.edit'))
                                    <a href="{{ route('hr.departments.edit', $department) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('Edit') }}">
                                        <x-icon name="pencil" class="icon-sm" />
                                    </a>
                                @endif
                                @if (auth()->user()->hasPermission('hr.delete'))
                                    <form method="POST" action="{{ route('hr.departments.destroy', $department) }}" class="inline" onsubmit="return confirm('{{ __('Delete this department?') }}')">
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
                                <x-empty-state icon="layers" :title="__('No departments yet')" :message="__('Add your first department to start organizing staff.')">
                                    <a href="{{ route('hr.departments.create') }}" class="btn btn-primary btn-sm">{{ __('Add department') }}</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $departments->links() }}</div>
    </x-card>

</x-layouts.app>
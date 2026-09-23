<x-layouts.app :title="__('Training Records')">

    <x-page-header :title="__('Training Records')" :description="__('Professional development and training completed by staff.')">
        @if (auth()->user()->hasPermission('hr.create'))
            <a href="{{ route('hr.training.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                {{ __('Add training') }}
            </a>
        @endif
    </x-page-header>

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Course') }}</th>
                        <th>{{ __('Provider') }}</th>
                        <th>{{ __('Completed') }}</th>
                        <th>{{ __('Hours') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $record->employee->full_name }}</div>
                                <div class="text-xs text-muted">{{ $record->employee->employee_id }}</div>
                            </td>
                            <td>{{ $record->course_name }}</td>
                            <td class="text-sm text-muted">{{ $record->provider ?? '—' }}</td>
                            <td class="text-sm">{{ $record->completed_on?->format('M j, Y') ?? $record->trained_on?->format('M j, Y') ?? '—' }}</td>
                            <td class="text-sm">{{ $record->hours ?? '—' }}</td>
                            <td class="actions-cell">
                                @if (auth()->user()->hasPermission('hr.delete'))
                                    <form method="POST" action="{{ route('hr.training.destroy', $record) }}" class="inline" onsubmit="return confirm('{{ __('Delete this training record?') }}')">
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
                                <x-empty-state icon="book-open" :title="__('No training records yet')" :message="__('Add training and professional development for staff.')">
                                    <a href="{{ route('hr.training.create') }}" class="btn btn-primary btn-sm">{{ __('Add training') }}</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $records->links() }}</div>
    </x-card>

</x-layouts.app>
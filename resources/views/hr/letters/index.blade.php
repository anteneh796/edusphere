<x-layouts.app :title="__('Official Letters')">

    <x-page-header :title="__('Official Letters')" :description="__('Employment, appointment, promotion and experience letters issued to staff.')">
        @if (auth()->user()->hasPermission('hr.create'))
            <a href="{{ route('hr.letters.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                {{ __('Issue letter') }}
            </a>
        @endif
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('hr.letters.index') }}" class="flex gap-1" style="align-items:flex-end; padding: var(--space-3);">
            <div class="form-group" style="margin:0; flex:1;">
                <label class="form-label" for="type">{{ __('Letter type') }}</label>
                <select id="type" name="type" class="form-select">
                    <option value="">{{ __('All types') }}</option>
                    @foreach (\App\Support\Enums\OfficialLetterType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
            @if (request('type'))
                <a href="{{ route('hr.letters.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
            @endif
        </form>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Issued on') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($letters as $letter)
                        <tr>
                            <td>
                                <a href="{{ route('hr.letters.show', $letter) }}" class="link">{{ $letter->reference_number }}</a>
                            </td>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $letter->employee->full_name }}</div>
                                <div class="text-xs text-muted">{{ $letter->employee->employee_id }}</div>
                            </td>
                            <td>{{ $letter->title }}</td>
                            <td>
                                <x-badge color="neutral">{{ $letter->typeEnum()?->label() ?? ucfirst(str_replace('_', ' ', $letter->letter_type)) }}</x-badge>
                            </td>
                            <td class="text-sm">{{ $letter->issued_on?->format('M j, Y') ?? '—' }}</td>
                            <td class="actions-cell">
                                <a href="{{ route('hr.letters.show', $letter) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('View') }}">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                                @if (auth()->user()->hasPermission('hr.delete'))
                                    <form method="POST" action="{{ route('hr.letters.destroy', $letter) }}" class="inline" onsubmit="return confirm('{{ __('Delete this letter?') }}')">
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
                                <x-empty-state icon="file-text" :title="__('No letters yet')" :message="__('Issue official letters for appointments, promotions and more.')">
                                    <a href="{{ route('hr.letters.create') }}" class="btn btn-primary btn-sm">{{ __('Issue letter') }}</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $letters->links() }}</div>
    </x-card>

</x-layouts.app>
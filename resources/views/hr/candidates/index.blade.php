<x-layouts.app :title="__('Recruitment Candidates')">

    <x-page-header :title="__('Recruitment')" :description="__('Manage applications, shortlists, interviews and hiring decisions.')">
        @if (auth()->user()->hasPermission('hr.create'))
            <a href="{{ route('hr.candidates.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                {{ __('Register candidate') }}
            </a>
        @endif
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('hr.candidates.index') }}" class="grid gap-1" style="grid-template-columns: 1fr 1fr auto auto; align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="q">{{ __('Search') }}</label>
                <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="{{ __('Name or position…') }}" class="form-control" />
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="status">{{ __('Status') }}</label>
                <select id="status" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (\App\Support\Enums\RecruitmentStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
            @if (request()->hasAny(['q', 'status']))
                <a href="{{ route('hr.candidates.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
            @endif
        </form>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Candidate') }}</th>
                        <th>{{ __('Applied for') }}</th>
                        <th>{{ __('Experience') }}</th>
                        <th>{{ __('Applied on') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($candidates as $candidate)
                        <tr>
                            <td>
                                <div class="flex" style="align-items:center; gap: var(--space-2);">
                                    <x-avatar :name="$candidate->full_name" size="sm" />
                                    <div>
                                        <a href="{{ route('hr.candidates.show', $candidate) }}" class="link" style="font-weight: var(--weight-semibold);">{{ $candidate->full_name }}</a>
                                        <div class="text-xs text-muted">{{ $candidate->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm">{{ $candidate->applying_for ?? '—' }}</td>
                            <td class="text-sm">{{ $candidate->experience_years ? $candidate->experience_years.' '.__('yrs') : '—' }}</td>
                            <td class="text-sm text-muted">{{ $candidate->applied_date?->format('M j, Y') ?? '—' }}</td>
                            <td>
                                <x-badge :color="$candidate->statusBadgeColor()" :dot="true">{{ $candidate->statusLabel() }}</x-badge>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('hr.candidates.show', $candidate) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('View') }}">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                                @if (auth()->user()->hasPermission('hr.delete'))
                                    <form method="POST" action="{{ route('hr.candidates.destroy', $candidate) }}" class="inline" onsubmit="return confirm('{{ __('Delete this candidate?') }}')">
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
                                <x-empty-state icon="users" :title="__('No candidates found')" :message="__('Register applications to build your recruitment pipeline.')">
                                    <a href="{{ route('hr.candidates.create') }}" class="btn btn-primary btn-sm">{{ __('Register candidate') }}</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $candidates->links() }}</div>
    </x-card>

</x-layouts.app>
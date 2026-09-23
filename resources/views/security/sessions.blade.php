<x-layouts.app :title="__('Active Sessions')">

    <x-page-header :title="__('Active Sessions')" :description="__('Currently signed-in users across devices. Revoke sessions you do not recognise.')" />

    <x-card>
        <form method="GET" action="{{ route('security.sessions') }}" class="grid-3" style="display:grid; grid-template-columns: 1fr auto; gap: var(--space-2); align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="q">{{ __('Search') }}</label>
                <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="{{ __('Name or IP address…') }}" class="form-control" />
            </div>
            <button type="submit" class="btn btn-primary">
                <x-icon name="search" class="icon-sm" />
                {{ __('Filter') }}
            </button>
        </form>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('IP address') }}</th>
                        <th>{{ __('Browser') }}</th>
                        <th>{{ __('Last activity') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $session->user_name ?? __('Unknown user') }}</div>
                                @if ($session->id === request()->session()->getId())
                                    <x-badge color="success">{{ __('This device') }}</x-badge>
                                @endif
                            </td>
                            <td class="text-sm">{{ $session->ip_address ?? '—' }}</td>
                            <td>
                                <div class="text-sm">{{ \App\Domains\Accounts\Models\LoginLog::query()->where('session_id', $session->id)->latest('login_at')->value('browser') ?? '—' }}</div>
                                <div class="text-xs text-muted">{{ \Illuminate\Support\Str::limit((string) $session->user_agent, 60) }}</div>
                            </td>
                            <td class="text-sm">{{ $session->last_activity ? \Illuminate\Support\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() : '—' }}</td>
                            <td class="actions-cell">
                                @if ($session->id !== request()->session()->getId())
                                    <form method="POST" action="{{ route('security.sessions.revoke', $session->id) }}">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $session->user_id }}">
                                        <button type="submit" class="btn btn-ghost btn-sm btn-icon text-danger" :title="__('Revoke')">
                                            <x-icon name="x" class="icon-sm" />
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="devices" :title="__('No active sessions')" :message="__('No signed-in sessions to display.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $sessions->links() }}
        </div>
    </x-card>

</x-layouts.app>
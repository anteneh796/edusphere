<x-layouts.app :title="__('Login History')">

    <x-page-header :title="__('Login History')" :description="__('Sign-in, failed, logout and expired-session events across the school.')" />

    <x-card>
        <form method="GET" action="{{ route('security.login-history') }}" class="grid-3" style="display:grid; grid-template-columns: 1fr 1fr auto; gap: var(--space-2); align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="q">{{ __('Search') }}</label>
                <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="{{ __('Name, identifier or IP…') }}" class="form-control" />
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="event">{{ __('Event') }}</label>
                <select id="event" name="event" class="form-select">
                    <option value="">{{ __('All events') }}</option>
                    @foreach (['login' => __('Login'), 'failed' => __('Failed'), 'logout' => __('Logout'), 'expired' => __('Expired')] as $value => $label)
                        <option value="{{ $value }}" @selected(request('event') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
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
                        <th>{{ __('Event') }}</th>
                        <th>{{ __('Identifier') }}</th>
                        <th>{{ __('Device') }}</th>
                        <th>{{ __('IP address') }}</th>
                        <th>{{ __('When') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $log->user?->full_name ?? '—' }}</div>
                            </td>
                            <td>
                                <x-badge :color="match ($log->event) {
                                    'login' => 'success',
                                    'failed' => 'danger',
                                    'logout' => 'neutral',
                                    'expired' => 'warning',
                                    default => 'neutral',
                                }">{{ \Illuminate\Support\Str::headline($log->event) }}</x-badge>
                            </td>
                            <td class="text-sm">{{ $log->identifier }}</td>
                            <td class="text-sm">
                                <div>{{ $log->device ?? '—' }}</div>
                                <div class="text-xs text-muted">{{ $log->browser ?? '' }} {{ $log->platform ?? '' }}</div>
                            </td>
                            <td class="text-sm">{{ $log->ip_address }}</td>
                            <td class="text-sm">
                                <div>{{ $log->login_at?->format('M j, H:i') }}</div>
                                @if ($log->logout_at)
                                    <div class="text-xs text-muted">{{ __('out').' '.$log->logout_at->format('M j, H:i') }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="clock" :title="__('No login events')" :message="__('Nothing matches your filters.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </x-card>

</x-layouts.app>
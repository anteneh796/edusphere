<x-layouts.app :title="'Parent dashboard'">
    <x-page-header
        title="Welcome, {{ $guardian?->relationshipLabel() ?? 'parent' }}"
        description="Manage your wards' school life from one place.">
        <a href="{{ route('portals.parent.billing') }}" class="btn btn-ghost">
            <x-icon name="receipt" class="icon-sm" />
            Billing
        </a>
    </x-page-header>

    @if (! $guardian)
        <x-card title="Account not linked">
            <x-empty-state
                icon="users"
                title="No guardian profile linked"
                message="Ask the registrar to link a guardian profile to your login so you can view your wards." />
        </x-card>
    @else
        <div class="grid grid-stats">
            <x-stat-card label="Wards" :value="$wards->count()" icon="users" color="primary" />
            @php $activeWards = $wards->filter(fn ($w) => strtolower((string) ($w->status ?? 'active')) === 'active')->count(); @endphp
            <x-stat-card label="Active wards" :value="$activeWards" icon="shield-check" color="success" />
            <x-stat-card label="Outstanding balance" :value="number_format($wards->sum(fn ($w) => (float) ($w->fee_balance ?? 0)), 2)" icon="receipt" color="warning" />
            <x-stat-card label="This month" :value="number_format($wards->sum(fn ($w) => (float) ($w->last_payment_amount ?? 0)), 2)" icon="arrow-down-right" color="accent" />
        </div>

        <div class="grid grid-2 mt-4">
            <x-card title="My wards">
                @forelse ($wards as $ward)
                    <div class="list-row">
                        <x-avatar :initials="$ward->initials()" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate">{{ $ward->full_name }}</div>
                            <div class="text-xs text-light">{{ $ward->student_number }} · {{ $ward->classRoom?->name }}</div>
                        </div>
                        <a href="{{ route('portals.parent.wards.show', $ward->student_number) }}" class="btn btn-ghost btn-sm">View</a>
                    </div>
                @empty
                    <x-empty-state icon="users" title="No wards yet" message="Once a guardian profile is linked, your wards will be listed here." />
                @endforelse
            </x-card>

            <x-card title="Latest ward activity">
                @forelse ($wards ?? collect() as $ward)
                    @php $latestResult = $ward?->latestResult; @endphp
                    <div class="list-row">
                        <x-icon name="award" class="icon-sm text-light" />
                        <div class="text-sm truncate">{{ $ward->full_name }}</div>
                        <span class="badge {{ $latestResult ? 'badge-success' : 'badge-neutral' }}">
                            {{ $latestResult?->subject?->name ?? 'No results yet' }}
                        </span>
                    </div>
                @empty
                    <x-empty-state icon="activity" title="No activity" message="Ward updates will show here." />
                @endforelse
            </x-card>
        </div>

        <div class="mt-4">
            <x-card title="Quick actions">
                <div class="portal-actions">
                    <a href="{{ route('portals.parent.wards') }}" class="btn btn-ghost">
                        <x-icon name="users" class="icon-sm" />
                        View all wards
                    </a>
                    <a href="{{ route('portals.parent.wards.show', $wards->first()?->student_number) }}" class="btn btn-ghost {{ $wards->isEmpty() ? 'is-disabled' : '' }}">
                        <x-icon name="user" class="icon-sm" />
                        Ward snapshot
                    </a>
                    <a href="{{ route('portals.parent.billing') }}" class="btn btn-ghost">
                        <x-icon name="receipt" class="icon-sm" />
                        Billing
                    </a>
                </div>
            </x-card>
        </div>
    @endif
</x-layouts.app>

<x-layouts.app :title="$user->full_name">
    <x-breadcrumb :items="[
        ['label' => __('Staff'), 'url' => route('staff.index')],
        ['label' => $user->full_name],
    ]" />

    <x-page-header :title="$user->full_name" :description="$user->job_title ?? $user->email">
        @if (auth()->user()->hasPermission('staff.edit'))
            <a href="{{ route('staff.edit', $user) }}" class="btn btn-primary btn-sm">
                <x-icon name="pencil" class="icon-sm" />
                {{ __('Edit') }}
            </a>
        @endif
    </x-page-header>

    <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-3);">
        <x-card>
            <div class="avatar-cell" style="margin-bottom: var(--space-3);">
                <x-avatar :initials="$user->initials()" size="lg" />
                <div>
                    <div style="font-weight: var(--weight-semibold);">{{ $user->full_name }}</div>
                    <div class="text-sm text-muted">{{ $user->job_title ?? __('No job title') }}</div>
                </div>
            </div>

            <dl class="detail-list">
                <dt>{{ __('Email') }}</dt>
                <dd>{{ $user->email }}</dd>
                <dt>{{ __('Phone') }}</dt>
                <dd>{{ $user->phone ?? '—' }}</dd>
                <dt>{{ __('Employee ID') }}</dt>
                <dd>{{ $user->employee_id ?? '—' }}</dd>
                <dt>{{ __('Staff type') }}</dt>
                <dd>{{ $user->staff_type ? \App\Support\Enums\StaffType::from($user->staff_type)->label() : '—' }}</dd>
                <dt>{{ __('Department') }}</dt>
                <dd>{{ $user->department ?? '—' }}</dd>
                <dt>{{ __('Hire date') }}</dt>
                <dd>{{ $user->hire_date?->format('M j, Y') ?? '—' }}</dd>
                <dt>{{ __('Contract end date') }}</dt>
                <dd>{{ $user->contract_end_date?->format('M j, Y') ?? '—' }}</dd>
                <dt>{{ __('Status') }}</dt>
                <dd>
                    <x-badge :color="match ($user->status) { 'active' => 'success', 'inactive' => 'warning', 'suspended' => 'danger', default => 'neutral' }" :dot="true">
                        {{ \Illuminate\Support\Str::headline($user->status) }}
                    </x-badge>
                </dd>
            </dl>
        </x-card>

        <x-card :title="__('Access & roles')">
            <div class="flex gap-1 flex-wrap" style="margin-bottom: var(--space-3);">
                @foreach ($user->roles as $role)
                    <x-badge color="{{ in_array($role->name, ['super_admin', 'school_admin', 'principal', 'vice_principal']) ? 'accent' : ($role->name === 'teacher' ? 'primary' : 'neutral') }}">{{ $role->label }}</x-badge>
                @endforeach
            </div>

            <dl class="detail-list">
                <dt>{{ __('Username') }}</dt>
                <dd>{{ $user->username ?? '—' }}</dd>
                <dt>{{ __('Last sign in') }}</dt>
                <dd>{{ $user->last_login_at?->format('M j, Y H:i') ?? 'Never' }}</dd>
                <dt>{{ __('Last sign in IP') }}</dt>
                <dd>{{ $user->last_login_ip ?? '—' }}</dd>
                <dt>{{ __('Password change required') }}</dt>
                <dd>{{ $user->must_change_password ? __('Yes') : __('No') }}</dd>
            </dl>

            <div class="flex gap-1">
                @if (auth()->user()->hasPermission('staff.edit'))
                    <form method="POST" action="{{ route('users.reset-password', $user) }}" onsubmit="return confirm('{{ __('Force password reset for this staff member?') }}');">
                        @csrf
                        <input type="hidden" name="password" value="">
                        <button type="submit" class="btn btn-secondary btn-sm">
                            <x-icon name="key" class="icon-sm" />
                            {{ __('Force password reset') }}
                        </button>
                    </form>
                @endif
            </div>
        </x-card>
    </div>
</x-layouts.app>

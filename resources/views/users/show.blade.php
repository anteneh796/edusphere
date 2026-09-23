<x-layouts.app :title="'View user'">
    <x-breadcrumb :items="[
        ['label' => 'Users & Roles', 'url' => route('users.index')],
        ['label' => $user->full_name],
    ]" />

    <x-page-header :title="$user->full_name" description="Account overview for {{ $user->email }}.">
        @can('update', $user)
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                <x-icon name="pencil" class="icon-sm" />
                Edit user
            </a>
        @endcan
    </x-page-header>

    <div class="grid-2">
        <x-card>
            <div class="flex items-center gap-3" style="padding: var(--space-2) 0;">
                <x-avatar :initials="$user->initials()" size="xl" />
                <div>
                    <div style="font-size: var(--text-lg); font-weight: var(--weight-bold);">{{ $user->full_name }}</div>
                    <div class="text-sm text-muted">{{ $user->email }}</div>
                    <div class="flex gap-1 mt-1 flex-wrap">
                        @foreach ($user->roles as $role)
                            <x-badge :color="'primary'">{{ $role->label }}</x-badge>
                        @endforeach
                    </div>
                </div>
            </div>

            <hr>

            <dl class="grid grid-2" style="gap: var(--space-2);">
                <div>
                    <dt class="text-xs text-muted">First name</dt>
                    <dd class="font-semibold">{{ $user->first_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted">Last name</dt>
                    <dd class="font-semibold">{{ $user->last_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted">Phone</dt>
                    <dd class="font-semibold">{{ $user->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted">Username</dt>
                    <dd class="font-semibold">{{ $user->username ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted">Employee ID</dt>
                    <dd class="font-semibold">{{ $user->employee_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted">Student number</dt>
                    <dd class="font-semibold">{{ $user->student_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted">Last sign-in</dt>
                    <dd class="font-semibold">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted">Status</dt>
                    <dd>
                        <x-badge :color="match ($user->status) { 'active' => 'success', 'inactive' => 'warning', 'suspended' => 'danger', default => 'neutral' }" :dot="true">
                            {{ \Illuminate\Support\Str::headline($user->status) }}
                        </x-badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-muted">Member since</dt>
                    <dd class="font-semibold">{{ $user->created_at->format('M j, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted">Last updated</dt>
                    <dd class="font-semibold">{{ $user->updated_at->diffForHumans() }}</dd>
                </div>
            </dl>
        </x-card>

        <x-card title="Access summary">
            <div class="mb-3">
                <div class="section-heading"><x-icon name="shield" class="icon-sm" /><h3 class="text-sm">Assigned roles</h3></div>
                <div class="flex flex-col" style="gap: 8px;">
                    @foreach ($user->roles as $role)
                        <div class="flex items-center justify-between" style="padding: var(--space-2); background: var(--color-surface-muted); border-radius: var(--radius-sm);">
                            <div>
                                <div class="font-semibold text-sm">{{ $role->label }}</div>
                                <div class="text-xs text-muted">{{ $role->permissions_count ?? $role->permissions()->count() }} permissions</div>
                            </div>
                            <x-icon name="check-circle" class="icon-sm text-success" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="help-card">
                Permissions are derived from roles. A Super Admin bypasses permission checks automatically.
            </div>
        </x-card>
    </div>

    @can('update', $user)
        <x-card title="Reset password" class="mt-2">
            <p class="text-sm text-muted mb-2">
                Resetting the password signs the user out everywhere and requires them to set a new
                password on their next sign-in. Leave the field blank to generate a random one.
            </p>
            <form method="POST" action="{{ route('users.reset-password', $user) }}" class="grid-2" style="align-items: end;">
                @csrf
                <div class="flex flex-col" style="gap: var(--space-2);">
                    <x-input name="password" type="password" label="New password (optional)" :placeholder="'Leave blank to generate'" autocomplete="off" />
                    <x-input name="password_confirmation" type="password" label="Confirm password" autocomplete="off" />
                </div>
                <button type="submit" class="btn btn-outline-danger">
                    <x-icon name="refresh" class="icon-sm" />
                    Reset password
                </button>
            </form>
        </x-card>
    @endcan
</x-layouts.app>
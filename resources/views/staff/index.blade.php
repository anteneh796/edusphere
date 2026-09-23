<x-layouts.app :title="__('Staff')">

    <x-page-header :title="__('Staff')" :description="__('Manage teachers and school staff, their roles and staff records.')">
        <a href="{{ route('staff.create') }}" class="btn btn-primary">
            <x-icon name="user-plus" class="icon-sm" />
            {{ __('Add staff') }}
        </a>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('staff.index') }}" id="staff-filters" class="grid-4" style="display:grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: var(--space-2); align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="q">{{ __('Search') }}</label>
                <input type="search" id="q" name="q" value="{{ request('q') }}" :placeholder="__('Name, email or employee ID…')" class="form-control" />
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="role-filter">{{ __('Role') }}</label>
                <select id="role-filter" name="role" class="form-select">
                    <option value="">{{ __('All roles') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="type-filter">{{ __('Staff type') }}</label>
                <select id="type-filter" name="staff_type" class="form-select">
                    <option value="">{{ __('All types') }}</option>
                    @foreach (\App\Support\Enums\StaffType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(request('staff_type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="status-filter">{{ __('Status') }}</label>
                <select id="status-filter" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <div class="flex gap-1" style="align-items:end; padding: 0 var(--space-3) var(--space-3);">
            <button type="submit" class="btn btn-primary" form="staff-filters">
                <x-icon name="search" class="icon-sm" />
                {{ __('Filter') }}
            </button>
            @if (request('q') || request('role') || request('staff_type') || request('status'))
                <a href="{{ route('staff.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
            @endif
        </div>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Staff member') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $user)
                        <tr>
                            <td>
                                <div class="avatar-cell">
                                    <x-avatar :initials="$user->initials()" size="sm" />
                                    <div style="min-width:0;">
                                        <div style="font-weight: var(--weight-semibold);">{{ $user->full_name }}</div>
                                        <div class="text-xs text-muted">{{ $user->employee_id ?: $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-1 flex-wrap">
                                    @foreach ($user->roles as $role)
                                        <x-badge color="{{ in_array($role->name, ['super_admin', 'school_admin', 'principal', 'vice_principal']) ? 'accent' : ($role->name === 'teacher' ? 'primary' : 'neutral') }}">{{ $role->label }}</x-badge>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-sm text-muted">
                                {{ $user->staff_type ? \App\Support\Enums\StaffType::from($user->staff_type)->label() : '—' }}
                            </td>
                            <td class="text-sm text-muted">{{ $user->department ?? '—' }}</td>
                            <td class="text-sm">
                                <div>{{ $user->phone ?? '—' }}</div>
                                <div class="text-xs text-muted">{{ $user->email }}</div>
                            </td>
                            <td>
                                <x-badge :color="match ($user->status) { 'active' => 'success', 'inactive' => 'warning', 'suspended' => 'danger', default => 'neutral' }" :dot="true">
                                    {{ \Illuminate\Support\Str::headline($user->status) }}
                                </x-badge>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('staff.show', $user) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('View')">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                                @if (auth()->user()->hasPermission('staff.edit'))
                                    <a href="{{ route('staff.edit', $user) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('Edit')">
                                        <x-icon name="pencil" class="icon-sm" />
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="users" :title="__('No staff found')" :message="__('Try adjusting your filters or add a new staff member.')">
                                    <a href="{{ route('staff.create') }}" class="btn btn-primary btn-sm">{{ __('Add staff') }}</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $staff->links() }}
        </div>
    </x-card>

</x-layouts.app>
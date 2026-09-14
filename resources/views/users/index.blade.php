<x-layouts.app :title="'Users & Roles'">

    <x-page-header title="Users & Roles" description="Manage staff, teachers and account access across the school.">
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <x-icon name="user-plus" class="icon-sm" />
            Add User
        </a>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('users.index') }}" class="grid-3" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: var(--space-2); align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="q">Search</label>
                <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Name, email or phone…" class="form-control" />
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="role-filter">Role</label>
                <select id="role-filter" name="role" class="form-select">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-1" style="align-items:end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="search" class="icon-sm" />
                    Filter
                </button>
                @if (request('q') || request('role') || request('status'))
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Roles</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="avatar-cell">
                                    <x-avatar :initials="$user->initials()" size="sm" />
                                    <div style="min-width:0;">
                                        <div style="font-weight: var(--weight-semibold);">{{ $user->full_name }}</div>
                                        <div class="text-xs text-muted">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-1 flex-wrap">
                                    @foreach ($user->roles as $role)
                                        @php
                                            $colors = [
                                                'super_admin' => 'accent',
                                                'principal' => 'primary',
                                                'registrar' => 'info',
                                                'teacher' => 'accent',
                                                'accountant' => 'success',
                                                'parent' => 'neutral',
                                                'student' => 'neutral',
                                            ];
                                        @endphp
                                        <x-badge :color="$colors[$role->name] ?? 'neutral'">{{ $role->label }}</x-badge>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-sm">
                                <div>{{ $user->phone ?? '—' }}</div>
                            </td>
                            <td>
                                <x-badge :color="match ($user->status) { 'active' => 'success', 'inactive' => 'warning', 'suspended' => 'danger', default => 'neutral' }" :dot="true">
                                    {{ \Illuminate\Support\Str::headline($user->status) }}
                                </x-badge>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-ghost btn-sm btn-icon" title="View">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-ghost btn-sm btn-icon" title="Edit">
                                    <x-icon name="pencil" class="icon-sm" />
                                </a>
                                @can('delete', $user)
                                    <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger"
                                        title="Delete"
                                        @click="$store.confirm.ask({
                                            title: 'Delete user?',
                                            message: `Delete ${@js($user->full_name)}. This cannot be undone.`,
                                            action: @js(route('users.destroy', $user)),
                                            method: 'DELETE',
                                            confirmText: 'Delete'
                                        })">
                                        <x-icon name="trash" class="icon-sm" />
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="users" title="No users found" message="Try adjusting your filters or add a new user.">
                                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">Add User</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $users->links() }}
        </div>
    </x-card>

</x-layouts.app>
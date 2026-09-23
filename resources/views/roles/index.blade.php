<x-layouts.app :title="__('Role Permissions')">

    <x-page-header :title="__('Role Permissions')" :description="__('Fine-tune what each role can access. Defaults come from the RBAC policy.')" />

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Key') }}</th>
                        <th>{{ __('Permissions') }}</th>
                        <th>{{ __('Users') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $role->label }}</div>
                                <div class="text-xs text-muted">{{ $role->description }}</div>
                            </td>
                            <td>
                                <x-badge color="neutral">{{ $role->name }}</x-badge>
                            </td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td class="actions-cell">
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('Edit')">
                                    <x-icon name="pencil" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="shield" :title="__('No roles found')" :message="__('Run the roles and permissions seeder to create the default roles.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

</x-layouts.app>
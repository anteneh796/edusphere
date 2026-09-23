<x-layouts.app :title="__('Edit role')">
    <x-breadcrumb :items="[
        ['label' => __('Role Permissions'), 'url' => route('roles.index')],
        ['label' => $role->label],
    ]" />

    <x-page-header :title="$role->label" :description="__('Permissions assigned to the').' '.$role->label.' '.__('role.')">
        <x-badge color="neutral">{{ $role->name }}</x-badge>
    </x-page-header>

    @php
        $locked = config('rbac.roles.'.$role->name, []);
        $selected = $role->permissions->pluck('name')->all();
        $isLocked = ! empty($locked);
    @endphp

    <div style="max-width: 880px;">
        <form method="POST" action="{{ route('roles.update', $role) }}" novalidate>
            @csrf
            @method('PUT')

            @if ($isLocked)
                <x-alert type="info" class="mb-2">
                    {{ __('This is a permanent role. Its default permissions are locked; checking extra permissions below expands its access.') }}
                </x-alert>
            @endif

            @foreach ($permissions as $module => $modulePermissions)
                <x-card :title="__(\Illuminate\Support\Str::headline($module))">
                    <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: 2px;">
                        @foreach ($modulePermissions as $permission)
                            @php($isDefault = in_array($permission->name, $locked, true))
                            <label class="form-check" title="{{ $isDefault ? __('Required default permission') : $permission->name }}">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->name }}"
                                    @checked($isDefault || in_array($permission->name, $selected, true))
                                    @disabled($isDefault && $isLocked) />
                                <span>{{ str($permission->name)->after('.')->headline() }}</span>
                            </label>
                        @endforeach
                    </div>
                </x-card>
            @endforeach

            <x-card>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="check" class="icon-sm" />
                    {{ __('Save permissions') }}
                </button>
            </x-card>
        </form>
    </div>
</x-layouts.app>
@php
    $editing = isset($user);
    $selectedRoles = old('roles', $editing ? $user->roles->pluck('id')->all() : []);
@endphp

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <div class="flex flex-col" style="gap: var(--space-2);">
        <x-input name="first_name" label="First name" :value="old('first_name', $editing ? $user->first_name : null)" placeholder="e.g. Abebe" required />
        <x-input name="last_name" label="Last name" :value="old('last_name', $editing ? $user->last_name : null)" placeholder="e.g. Bekele" required />
        <x-input name="email" type="email" label="Email address" :value="old('email', $editing ? $user->email : null)" placeholder="abebe@school.et" required />
        <x-input name="phone" label="Phone number" :value="old('phone', $editing ? $user->phone : null)" placeholder="+251 9xx xxx xxx" />
    </div>

    <div class="flex flex-col" style="gap: var(--space-2);">
        <div class="form-group">
            <label class="form-label">Roles <span class="required">*</span></label>
            <div @class(['flex flex-col', 'forms' => true]) style="gap: 2px; border:1px solid var(--color-border); border-radius: var(--control-radius); padding: var(--space-1) var(--space-2); background: var(--color-surface);">
                @foreach ($roleOptions as $role)
                    <label class="form-check" style="padding:6px 0;">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, $selectedRoles)) />
                        <span>{{ $role->label }}</span>
                    </label>
                @endforeach
            </div>
            @error('roles')
                <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
            @enderror
        </div>

        @if ($editing)
            <div class="form-hint">Leave the password fields blank to keep the current password.</div>
        @else
            <div class="form-hint">The user will use this password on first sign in.</div>
        @endif
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-input name="password" type="password" label="{{ $editing ? 'New password' : 'Password' }}"
        placeholder="{{ $editing ? 'Leave blank to keep current' : 'Minimum 8 characters' }}"
        :required="! $editing" autocomplete="new-password" />
    <x-input name="password_confirmation" type="password" label="Confirm password"
        placeholder="Repeat password"
        :required="! $editing" autocomplete="new-password" />
</div>

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <div class="form-group">
        <label class="form-label" for="status">Status</label>
        <select id="status" name="status" class="form-select" required>
            <option value="">Select status…</option>
            @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $editing ? $user->status : 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
        @enderror
    </div>
</div>
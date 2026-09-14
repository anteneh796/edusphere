@php
    $editing = isset($guardian) && $guardian !== null;
    $guardian ??= null;
@endphp

<h3 class="section-title" style="margin-bottom: var(--space-3);">Guardian details</h3>

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-input name="first_name" label="First name" :value="old('first_name', $editing ? $guardian->first_name : null)" placeholder="e.g. Almaz" required />
    <x-input name="last_name" label="Last name" :value="old('last_name', $editing ? $guardian->last_name : null)" placeholder="e.g. Bekele" required />
    <x-select name="relationship" label="Relationship" :options="$relationshipOptions" :value="old('relationship', $editing ? $guardian->relationship : null)" placeholder="Select relationship…" required />
    <x-input name="phone" label="Phone" :value="old('phone', $editing ? $guardian->phone : null)" placeholder="+251 9xx xxx xxx" />
    <x-input name="email" type="email" label="Email" :value="old('email', $editing ? $guardian->email : null)" placeholder="Optional" />
    <x-input name="occupation" label="Occupation" :value="old('occupation', $editing ? $guardian->occupation : null)" placeholder="Optional" />
    <x-input name="national_id" label="National ID" :value="old('national_id', $editing ? $guardian->national_id : null)" placeholder="Optional" />
</div>

<div class="grid" style="grid-template-columns: 1fr;">
    <x-input name="address" label="Address" :value="old('address', $editing ? $guardian->address : null)" placeholder="City, sub-city…" />
</div>
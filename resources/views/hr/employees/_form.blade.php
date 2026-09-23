@php
    $editing = isset($employee) && $employee !== null;
    $employee ??= null;
    $selectedRoles = old('roles', $editing ? ($employee->user?->roles->pluck('id')->all() ?? []) : []);
    $statuses = collect(\App\Support\Enums\EmploymentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]);
    $types = collect(\App\Support\Enums\EmploymentType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]);
    $positionsByDept = $positions->groupBy(fn ($p) => $p->department_id);
@endphp

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-3);">
    <div class="flex flex-col" style="gap: var(--space-2);">
        <h3 class="text-sm" style="font-weight: var(--weight-semibold); margin:0;">{{ __('Personal information') }}</h3>
        <x-input name="full_name" label="{{ __('Full name') }}" :value="old('full_name', $editing ? $employee->full_name : null)" placeholder="e.g. Abebe Bekele" required />
        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-2);">
            <x-select name="gender" :label="__('Gender')" :options="['male' => __('Male'), 'female' => __('Female')]" :value="old('gender', $editing ? $employee->gender : null)" placeholder="{{ __('Select…') }}" />
            <x-input name="date_of_birth" type="date" label="{{ __('Date of birth') }}" :value="old('date_of_birth', $editing && $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : null)" />
        </div>
        <x-input name="national_id" label="{{ __('National ID') }}" :value="old('national_id', $editing ? $employee->national_id : null)" placeholder="e.g. 12345678" />
        <x-input name="phone" label="{{ __('Phone number') }}" :value="old('phone', $editing ? $employee->phone : null)" placeholder="+251 9xx xxx xxx" />
        <x-input name="email" type="email" label="{{ __('Email address') }}" :value="old('email', $editing ? $employee->email : null)" placeholder="abebe@school.et" hint="{{ $editing && $employee->user ? __('This employee already has a login linked to this email.') : null }}" />
        <x-input name="address" label="{{ __('Address') }}" :value="old('address', $editing ? $employee->address : null)" placeholder="City, Sub-city, Woreda" />
        <div class="form-group">
            <label class="form-label" for="photo">{{ __('Photo') }}</label>
            <input type="file" id="photo" name="photo" class="form-control" accept="image/*" />
            @error('photo')
                <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="flex flex-col" style="gap: var(--space-2);">
        <h3 class="text-sm" style="font-weight: var(--weight-semibold); margin:0;">{{ __('Employment') }}</h3>
        <x-select name="department_id" :label="__('Department')" :options="$departments->pluck('name', 'id')" :value="old('department_id', $editing ? $employee->department_id : null)" placeholder="{{ __('Select department…') }}" />
        <x-select name="position_id" :label="__('Position')" :options="$positionsByDept" :value="old('position_id', $editing ? $employee->position_id : null)" placeholder="{{ __('Select position…') }}" />
        <x-select name="supervisor_id" :label="__('Reports to')" :options="$supervisors->mapWithKeys(fn ($s) => [$s->id => $s->full_name.' ('.$s->employee_id.')'])" :value="old('supervisor_id', $editing ? $employee->supervisor_id : null)" placeholder="{{ __('Select supervisor (optional)…') }}" />
        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-2);">
            <x-select name="employment_type" :label="__('Employment type')" :options="$types" :value="old('employment_type', $editing ? $employee->employment_type : 'full_time')" required />
            <x-select name="employment_status" :label="__('Employment status')" :options="$statuses" :value="old('employment_status', $editing ? $employee->employment_status : 'active')" required />
        </div>
        <x-input name="joining_date" type="date" label="{{ __('Joining date') }}" :value="old('joining_date', $editing && $employee->joining_date ? $employee->joining_date->format('Y-m-d') : null)" />

        <h3 class="text-sm" style="font-weight: var(--weight-semibold); margin: var(--space-2) 0 0;">{{ __('Academic background') }}</h3>
        <x-input name="university" label="{{ __('University') }}" :value="old('university', $editing ? $employee->university : null)" placeholder="e.g. AAU" />
        <x-input name="degree" label="{{ __('Degree') }}" :value="old('degree', $editing ? $employee->degree : null)" placeholder="e.g. BA, MSc" />
        <x-input name="specialization" label="{{ __('Specialization') }}" :value="old('specialization', $editing ? $employee->specialization : null)" placeholder="e.g. Mathematics" />
        <x-input name="teaching_license" label="{{ __('Teaching license') }}" :value="old('teaching_license', $editing ? $employee->teaching_license : null)" placeholder="e.g. License number" />
        <x-input name="years_of_experience" type="number" label="{{ __('Years of experience') }}" :value="old('years_of_experience', $editing ? $employee->years_of_experience : null)" min="0" max="70" />
        <x-textarea name="certifications" label="{{ __('Certifications') }}" :rows="2" :placeholder="__('One per line')">{{ old('certifications', $editing ? $employee->certifications : null) }}</x-textarea>
        <x-textarea name="professional_skills" label="{{ __('Professional skills') }}" :rows="2" :placeholder="__('One per line')">{{ old('professional_skills', $editing ? $employee->professional_skills : null) }}</x-textarea>
    </div>
</div>

<hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />

@if ($editing && $employee->user)
    <div class="form-hint">
        {{ __('This employee is already linked to a login account (:email). Edit the account from Users &amp; Roles if needed.', ['email' => $employee->user->email]) }}
    </div>
@else
    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-2);">
        <div class="form-group">
            <label class="form-check" style="gap: var(--space-1);">
                <input type="checkbox" name="create_account" value="1" @checked(old('create_account')) />
                <span>{{ __('Create a login account for this employee') }}</span>
            </label>
            <div class="form-hint">{{ __('Provide an email to build a user account (e.g. teacher or reception role).') }}</div>
        </div>
        <div class="flex flex-col" style="gap: var(--space-2);">
            <div class="flex flex-col" style="gap: var(--space-2);">
                <label class="form-label" for="roles">{{ __('Account role') }}</label>
                <div style="display:flex; flex-direction:column; gap:2px; border:1px solid var(--color-border); border-radius: var(--control-radius); padding: var(--space-1) var(--space-2); background: var(--color-surface);">
                    @foreach ($roles as $role)
                        <label class="form-check" style="padding:4px 0;">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, $selectedRoles)) />
                            <span>{{ $role->label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('roles')
                    <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
                @enderror
            </div>
            <x-input name="password" type="password" label="{{ __('Temporary password') }}" placeholder="{{ __('Minimum 8 characters') }}" autocomplete="new-password" />
            <x-input name="password_confirmation" type="password" label="{{ __('Confirm password') }}" placeholder="{{ __('Repeat password') }}" autocomplete="new-password" />
        </div>
    </div>
@endif
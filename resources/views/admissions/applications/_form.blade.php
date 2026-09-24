@php
$editing = isset($application);
$application ??= null;
$genderOptions = ['male' => 'Male', 'female' => 'Female'];
$relationshipOptions = [
    'father' => 'Father',
    'mother' => 'Mother',
    'guardian' => 'Parent',
    'sibling' => 'Sibling',
];

$parentsBackup = $editing
    ? $application->parents->map(fn ($guardian) => [
        'id' => $guardian->id,
        'first_name' => $guardian->first_name,
        'last_name' => $guardian->last_name,
        'relationship' => $guardian->relationship,
        'phone' => $guardian->phone,
        'email' => $guardian->email,
        'occupation' => $guardian->occupation,
        'national_id' => $guardian->national_id,
        'address' => $guardian->address,
        'is_primary' => (bool) $guardian->is_primary,
        'is_emergency' => (bool) $guardian->is_emergency,
    ])->values()->all()
    : (old('parents')
        ? array_map(fn ($row) => [
            'id' => $row['id'] ?? '',
            'first_name' => $row['first_name'] ?? '',
            'last_name' => $row['last_name'] ?? '',
            'relationship' => $row['relationship'] ?? '',
            'phone' => $row['phone'] ?? '',
            'email' => $row['email'] ?? '',
            'occupation' => $row['occupation'] ?? '',
            'national_id' => $row['national_id'] ?? '',
            'address' => $row['address'] ?? '',
            'is_primary' => (bool) ($row['is_primary'] ?? false),
            'is_emergency' => (bool) ($row['is_emergency'] ?? false),
        ], old('parents'))
        : []);
@endphp

@if (isset($inquiry) && $inquiry)
    <div class="alert alert-info">
        <x-icon name="info" class="icon-sm" />
        {{ __('Created from a website inquiry (:name, :email). The source is linked to this application.') }}
    </div>
    <input type="hidden" name="source_inquiry_id" value="{{ $inquiry->id }}" />
@endif

@if ($editing && $application->source_inquiry_id)
    <input type="hidden" name="source_inquiry_id" value="{{ $application->source_inquiry_id }}" />
@endif

<div x-data="parentSlots(@js($parentsBackup))">

    <h3 class="section-title">{{ __('Personal details') }}</h3>

    <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
        <x-input name="first_name" label="{{ __('First name') }}" :value="old('first_name', $editing ? $application->first_name : null)" placeholder="e.g. Abebe" required />
        <x-input name="last_name" label="{{ __('Last name') }}" :value="old('last_name', $editing ? $application->last_name : null)" placeholder="e.g. Bekele" required />
        <x-input name="other_names" label="{{ __('Other names') }}" :value="old('other_names', $editing ? $application->other_names : null)" placeholder="{{ __('Optional middle names') }}" />
        <x-select name="gender" label="{{ __('Gender') }}" :options="$genderOptions" :value="old('gender', $editing ? $application->gender : null)" placeholder="{{ __('Select gender…') }}" />
        <x-input name="date_of_birth" type="date" label="{{ __('Date of birth') }}" :value="old('date_of_birth', $editing ? optional($application->date_of_birth)->format('Y-m-d') : null)" />
        <x-input name="national_id" label="{{ __('National ID') }}" :value="old('national_id', $editing ? $application->national_id : null)" placeholder="{{ __('Optional') }}" />
        <x-input name="previous_school" label="{{ __('Previous school') }}" :value="old('previous_school', $editing ? $application->previous_school : null)" placeholder="{{ __('Optional') }}" />
        <x-input name="address" label="{{ __('Address') }}" :value="old('address', $editing ? $application->address : null)" placeholder="{{ __('City, sub-city…') }}" />
    </div>

    <h3 class="section-title">{{ __('Intended placement') }}</h3>

    <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
        <div class="form-group">
            <label class="form-label" for="intake_academic_year_id">{{ __('Intake year') }}</label>
            <select id="intake_academic_year_id" name="intake_academic_year_id" class="form-select">
                <option value="">{{ __('Current year') }}</option>
                @foreach ($years as $year)
                    <option value="{{ $year->id }}" @selected(old('intake_academic_year_id', $editing ? $application->intake_academic_year_id : null) === $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
            @error('intake_academic_year_id')
                <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="grade_level_id">{{ __('Grade') }}</label>
            <select id="grade_level_id" name="grade_level_id" class="form-select">
                <option value="">{{ __('Select grade…') }}</option>
                @foreach ($gradeLevels as $grade)
                    <option value="{{ $grade->id }}" @selected(old('grade_level_id', $editing ? $application->grade_level_id : null) === $grade->id)>{{ $grade->name }}</option>
                @endforeach
            </select>
            @error('grade_level_id')
                <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
            @enderror
        </div>
    </div>

    <h3 class="section-title">{{ __('Parents') }}</h3>

    <template x-for="(guardian, index) in parents" :key="index">
        <div class="card" style="margin-bottom: var(--space-2);">
            <div class="card-header">
                <h3 class="card-title" x-text="`{{ __('Parent') }} ${index + 1}`"></h3>
                <div class="card-actions">
                    <button type="button" class="btn btn-ghost btn-sm" @click="removeParent(index)">
                        <x-icon name="trash" class="icon-sm" />
                    </button>
                </div>
            </div>
            <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                <input type="hidden" :name="`parents[${index}][id]`" x-model="guardian.id" />
                <div class="form-group">
                    <label class="form-label">{{ __('First name') }} <span class="required">*</span></label>
                    <input type="text" class="form-control" :name="`parents[${index}][first_name]`" x-model="guardian.first_name" required />
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Last name') }} <span class="required">*</span></label>
                    <input type="text" class="form-control" :name="`parents[${index}][last_name]`" x-model="guardian.last_name" required />
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Relationship') }} <span class="required">*</span></label>
                    <select class="form-select" :name="`parents[${index}][relationship]`" x-model="guardian.relationship" required>
                        <option value="">{{ __('Select relationship…') }}</option>
                        @foreach ($relationshipOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Phone') }}</label>
                    <input type="text" class="form-control" :name="`parents[${index}][phone]`" x-model="guardian.phone" placeholder="+251 9xx xxx xxx" />
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" class="form-control" :name="`parents[${index}][email]`" x-model="guardian.email" placeholder="{{ __('Optional') }}" />
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Occupation') }}</label>
                    <input type="text" class="form-control" :name="`parents[${index}][occupation]`" x-model="guardian.occupation" placeholder="{{ __('Optional') }}" />
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('National ID') }}</label>
                    <input type="text" class="form-control" :name="`parents[${index}][national_id]`" x-model="guardian.national_id" placeholder="{{ __('Optional') }}" />
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Address') }}</label>
                    <input type="text" class="form-control" :name="`parents[${index}][address]`" x-model="guardian.address" placeholder="{{ __('Optional') }}" />
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-check">
                        <input type="checkbox" :name="`parents[${index}][is_primary]`" value="1" :checked="guardian.is_primary" @change="setPrimary(index, $event.target.checked)" />
                        <span>{{ __('Primary guardian') }}</span>
                    </label>
                    <label class="form-check" style="margin-left: var(--space-3);">
                        <input type="checkbox" :name="`parents[${index}][is_emergency]`" value="1" x-model="guardian.is_emergency" />
                        <span>{{ __('Emergency contact') }}</span>
                    </label>
                </div>
            </div>
        </div>
    </template>

    <div class="form-group">
        <button type="button" class="btn btn-secondary btn-sm" @click="addParent()">
            <x-icon name="plus" class="icon-sm" />
            {{ __('Add another guardian') }}
        </button>
    </div>

</div>

<script>
    function parentSlots(initial) {
        return {
            parents: initial.length ? initial : [parentSlots.empty()],
            addParent() {
                this.parents.push(parentSlots.empty());
            },
            removeParent(index) {
                if (this.parents.length > 1) {
                    this.parents.splice(index, 1);
                }
            },
            setPrimary(index, checked) {
                if (! checked) {
                    return;
                }
                this.parents.forEach((guardian, i) => {
                    guardian.is_primary = i === index;
                });
            }
        };
    }
    parentSlots.empty = function () {
        return {
            id: '', first_name: '', last_name: '', relationship: '', phone: '', email: '',
            occupation: '', national_id: '', address: '', is_primary: false, is_emergency: false
        };
    };
    window.parentSlots = parentSlots;
</script>
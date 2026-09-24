@php
    $editing = isset($student);
    $student ??= null;
    $guardian = $editing ? optional($student->primaryGuardian) : null;
    $genderOptions = ['male' => 'Male', 'female' => 'Female'];
    $relationshipOptions = [
        'father' => 'Father',
        'mother' => 'Mother',
        'guardian' => 'Guardian',
        'sibling' => 'Sibling',
    ];
@endphp

<div x-data="studentForm()">

    <h3 class="section-title">Personal details</h3>

    <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
        <x-input name="first_name" label="First name" :value="old('first_name', $editing ? $student->first_name : null)" placeholder="e.g. Abebe" required />
        <x-input name="last_name" label="Last name" :value="old('last_name', $editing ? $student->last_name : null)" placeholder="e.g. Bekele" required />
        <x-input name="other_names" label="Other names" :value="old('other_names', $editing ? $student->other_names : null)" placeholder="Optional middle names" />
        <x-select name="gender" label="Gender" :options="$genderOptions" :value="old('gender', $editing ? $student->gender : null)" placeholder="Select gender…" required />
        <x-input name="date_of_birth" type="date" label="Date of birth" :value="old('date_of_birth', $editing ? optional($student->date_of_birth)->format('Y-m-d') : null)" required />
        <x-input name="place_of_birth" label="Place of birth" :value="old('place_of_birth', $editing ? $student->place_of_birth : null)" placeholder="e.g. Addis Ababa" />
        <x-input name="national_id" label="National ID (FAN / FIN)" :value="old('national_id', $editing ? $student->national_id : null)" placeholder="Optional" />
        <x-input name="enrollment_date" type="date" label="Enrollment date" :value="old('enrollment_date', $editing ? optional($student->enrollment_date)->format('Y-m-d') : date('Y-m-d'))" required />
        <x-input name="previous_school" label="Previous school" :value="old('previous_school', $editing ? $student->previous_school : null)" placeholder="Optional" />
        <x-input name="address" label="Address" :value="old('address', $editing ? $student->address : null)" placeholder="City, sub-city…" />
        <x-input name="health_notes" label="Health notes" :value="old('health_notes', $editing ? $student->health_notes : null)" placeholder="Allergies, conditions…" />
        <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="photo">Profile photo</label>
            <input id="photo" name="photo" type="file" accept="image/*" class="form-select" />
            @if ($editing && $student->photo_path)
                <div class="form-hint">Current photo saved. Upload a new one to replace it.</div>
            @endif
            @error('photo')
                <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
            @enderror
        </div>
    </div>

    @if ($editing)
        <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
            <div class="form-group">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select" required>
                    @foreach ($studentStatuses ?? \App\Support\Enums\StudentStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $student->status) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                @error('status')
                    <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
                @enderror
            </div>
        </div>
    @endif

    <h3 class="section-title">Placement @if (! $editing)<span class="text-muted" style="font-size:13px; font-weight:400;">{{ '· '.(\App\Domains\Academics\Models\AcademicYear::current()->first()?->name ?? 'No active year') }}</span>@endif</h3>

    <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
        <div class="form-group">
            <label class="form-label" for="grade_level_id">Grade <span class="required">*</span></label>
            <select id="grade_level_id" name="grade_level_id" class="form-select" x-model="gradeLevel" @change="classRoom = ''" required>
                <option value="">Select grade…</option>
                @foreach ($gradeLevels as $grade)
                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                @endforeach
            </select>
            @error('grade_level_id')
                <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="class_room_id">Class <span class="required">*</span></label>
            <select id="class_room_id" name="class_room_id" class="form-select" x-model="classRoom" required>
                <option value="">Select class…</option>
                <template x-for="room in roomOptions()" :key="room.id">
                    <option :value="room.id" x-text="room.name"></option>
                </template>
            </select>
            @error('class_room_id')
                <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
            @enderror
        </div>
    </div>

    <h3 class="section-title">Primary parent</h3>

    <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
        <x-input name="guardian[first_name]" label="Parent first name" :value="old('guardian.first_name', $guardian ? $guardian->first_name : null)" placeholder="e.g. Almaz" :required="! $editing" :error="$errors->first('guardian.first_name')" />
        <x-input name="guardian[last_name]" label="Parent last name" :value="old('guardian.last_name', $guardian ? $guardian->last_name : null)" placeholder="e.g. Bekele" :required="! $editing" :error="$errors->first('guardian.last_name')" />
        <x-select name="guardian[relationship]" label="Relationship" :options="$relationshipOptions" :value="old('guardian.relationship', $guardian ? $guardian->relationship : null)" placeholder="Select relationship…" :required="! $editing" :error="$errors->first('guardian.relationship')" />
        <x-input name="guardian[phone]" label="Phone" :value="old('guardian.phone', $guardian ? $guardian->phone : null)" placeholder="+251 9xx xxx xxx" :error="$errors->first('guardian.phone')" />
        <x-input name="guardian[email]" type="email" label="Email" :value="old('guardian.email', $guardian ? $guardian->email : null)" placeholder="Optional" :error="$errors->first('guardian.email')" />
        <x-input name="guardian[occupation]" label="Occupation" :value="old('guardian.occupation', $guardian ? $guardian->occupation : null)" placeholder="Optional" :error="$errors->first('guardian.occupation')" />
    </div>

    @if ($editing)
        <div class="form-hint">Parent fields above update the primary parent. Leave blank to keep the current record unchanged.</div>
    @endif

</div>

<script>
    function studentForm() {
        return {
            gradeLevel: @js(old('grade_level_id', $editing ? ($student->grade_level_id ?? '') : '')),
            classRoom: @js(old('class_room_id', $editing ? ($student->class_room_id ?? '') : '')),
            classRooms: @js($classRooms),
            roomOptions() {
                return this.classRooms.filter((room) => room.grade_level_id === this.gradeLevel);
            }
        };
    }
    window.studentForm = studentForm;
</script>
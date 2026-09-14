@php $exam ??= null; @endphp

<div style="display:flex; flex-direction:column; gap: var(--space-4);">
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <div class="form-group">
            <label class="form-label" for="academic_year_id">Academic year</label>
            <select id="academic_year_id" name="academic_year_id" class="form-select">
                @foreach ($years as $year)
                    <option value="{{ $year->id }}" @selected(old('academic_year_id', $exam?->academic_year_id ?? $currentYearId ?? null) === $year->id)>
                        {{ $year->name }}{{ $year->is_current ? ' (current)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('academic_year_id')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="name">Exam name</label>
            <input id="name" type="text" name="name" class="form-input" value="{{ old('name', $exam?->name) }}" placeholder="e.g. Midterm Examination" required />
            @error('name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="type">Type</label>
            <select id="type" name="type" class="form-select" required>
                @foreach (\App\Support\Enums\ExamType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('type', $exam?->type?->value) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            @error('type')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <div class="form-group">
            <label class="form-label" for="start_date">Start date</label>
            <input id="start_date" type="date" name="start_date" class="form-input" value="{{ old('start_date', $exam?->start_date?->format('Y-m-d')) }}" required />
            @error('start_date')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="end_date">End date</label>
            <input id="end_date" type="date" name="end_date" class="form-input" value="{{ old('end_date', $exam?->end_date?->format('Y-m-d')) }}" />
            @error('end_date')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-select">
                @foreach (\App\Support\Enums\ExamStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $exam?->status?->value) === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            @error('status')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="form-group">
        <label class="form-label" for="description">Description</label>
        <textarea id="description" name="description" class="form-input form-textarea" rows="3" placeholder="Optional notes, coverage or instructions…" maxlength="500">{{ old('description', $exam?->description) }}</textarea>
        @error('description')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>
</div>
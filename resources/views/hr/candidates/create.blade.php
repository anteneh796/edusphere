<x-layouts.app :title="__('Register Candidate')">

    <x-page-header :title="__('Register Candidate')" :description="__('Add an applicant to the recruitment pipeline.')">
        <a href="{{ route('hr.candidates.index') }}" class="btn btn-secondary">{{ __('Back to candidates') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.candidates.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                <x-input name="name" label="{{ __('Full name') }}" :value="old('name')" placeholder="{{ __('e.g. Selam Tesfaye') }}" required />
                <x-input name="email" type="email" label="{{ __('Email') }}" :value="old('email')" required />
                <x-input name="phone" label="{{ __('Phone') }}" :value="old('phone')" />
                <x-select name="position_id" :label="__('Position (if advertised)')" :options="$positions->mapWithKeys(fn ($p) => [$p->id => $p->name])" placeholder="{{ __('Select…') }}" />
                <x-input name="applied_position" label="{{ __('Position applied for') }}" :value="old('applied_position')" placeholder="e.g. Mathematics Teacher" required style="grid-column: 1 / -1;" />
                <x-input name="experience_years" type="number" label="{{ __('Years of experience') }}" :value="old('experience_years')" min="0" step="0.5" placeholder="e.g. 5" />
                <x-input name="interview_date" type="date" label="{{ __('Interview date') }}" :value="old('interview_date')" />
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label" for="cv_path">{{ __('CV / resume') }}</label>
                    <input type="file" id="cv_path" name="cv_path" class="form-control" />
                    <div class="form-hint">{{ __('PDF, DOC or DOCX, max 5MB. Optional.') }}</div>
                </div>
                <x-textarea name="top_skills" label="{{ __('Top skills') }}" :rows="2" :value="old('top_skills')" placeholder="{{ __('Comma separated (e.g. Lesson planning, Classroom management)') }}" style="grid-column: 1 / -1;" />
                <x-textarea name="notes" label="{{ __('Notes') }}" :rows="3" style="grid-column: 1 / -1;" placeholder="{{ __('Optional notes…') }}">{{ old('notes') }}</x-textarea>
            </div>

            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.candidates.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Register candidate') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>
<x-layouts.app :title="__('New approval request')">
    <x-breadcrumb :items="[
        ['label' => __('Approvals'), 'url' => route('approvals.index')],
        ['label' => __('New request')],
    ]" />

    <x-page-header :title="__('New approval request')" :description="__('Submit a request for executive sign-off.')" />

    <div style="max-width: 720px;">
        <x-card>
            <form method="POST" action="{{ route('approvals.store') }}" novalidate
                x-data="{
                    type: '{{ old('type') }}',
                    subjectless: ['teacher_leave', 'fee_waiver', 'timetable_publication'],
                    needsStudent: ['student_transfer', 'admission_acceptance'],
                }">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="type">{{ __('Request type') }} <span class="required">*</span></label>
                    <select id="type" name="type" class="form-select {{ $errors->has('type') ? 'is-invalid' : '' }}" x-model="type" required>
                        <option value="">{{ __('Select type…') }}</option>
                        @foreach (\App\Support\Enums\ApprovalType::cases() as $approvalType)
                            <option value="{{ $approvalType->value }}">{{ $approvalType->label() }} — reviewed by {{ $approvalType->reviewerRole()->label() }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="form-error"><x-icon name="alert-circle" class="icon-sm" /> {{ $message }}</div>
                    @enderror
                </div>

                <div x-show="subjectless.includes(type)" class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                    <x-input name="leave_start" type="date" :label="__('Leave start')" :value="old('leave_start')" hint="Teacher leave only" />
                    <x-input name="leave_end" type="date" :label="__('Leave end')" :value="old('leave_end')" />
                    <x-input name="waiver_amount" type="number" step="0.01" min="0" :label="__('Waiver amount')" :value="old('waiver_amount')" hint="Fee waiver only" />
                </div>

                <div x-show="needsStudent.includes(type)" class="form-group">
                    <label class="form-label" for="student_id">{{ __('Student') }} <span class="required">*</span></label>
                    <select id="student_id" name="student_id" class="form-select {{ $errors->has('student_id') ? 'is-invalid' : '' }}">
                        <option value="">{{ __('Select student…') }}</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>
                                {{ $student->full_name ?? $student->first_name }} {{ $student->student_number }} — {{ $student->gradeLevel?->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <div class="form-error"><x-icon name="alert-circle" class="icon-sm" /> {{ $message }}</div>
                    @enderror
                </div>

                <div x-show="type === 'student_transfer'" class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                    <div class="form-group">
                        <label class="form-label" for="target_grade_id">{{ __('Target grade') }}</label>
                        <select id="target_grade_id" name="target_grade_id" class="form-select">
                            <option value="">{{ __('Unchanged') }}</option>
                            @foreach (\App\Domains\Academics\Models\GradeLevel::active()->ordered()->get() as $grade)
                                <option value="{{ $grade->id }}" @selected((string) old('target_grade_id') === (string) $grade->id)>{{ $grade->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="target_class_id">{{ __('Target class') }}</label>
                        <select id="target_class_id" name="target_class_id" class="form-select">
                            <option value="">{{ __('Unchanged') }}</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected((string) old('target_class_id') === (string) $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reason">{{ __('Reason') }}</label>
                    <textarea id="reason" name="reason" rows="3" class="form-input {{ $errors->has('reason') ? 'is-invalid' : '' }}" placeholder="Explain why this request is needed">{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="form-error"><x-icon name="alert-circle" class="icon-sm" /> {{ $message }}</div>
                    @enderror
                </div>

                <div class="card-footer">
                    <a href="{{ route('approvals.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="send" class="icon-sm" />
                        {{ __('Submit for approval') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
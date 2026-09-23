<x-layouts.app :title="__('New Report Card')">
    <x-breadcrumb :items="[
        ['label' => __('Report Cards'), 'url' => route('report-cards.index')],
        ['label' => __('New report card')],
    ]" />

    <x-page-header :title="__('New report card')" :description="__('Generate a report card for one student across every paper of an exam.')" />

    <div style="max-width: 720px;">
        <x-card>
            <form method="POST" action="{{ route('report-cards.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="exam_id">Exam</label>
                    <select id="exam_id" name="exam_id" class="form-select" required>
                        <option value="">Select exam…</option>
                        @foreach ($exams as $exam)
                            <option value="{{ $exam->id }}" @selected(old('exam_id') == $exam->id)>
                                {{ $exam->name }} · {{ $exam->academicYear?->name }}{{ $exam->status?->value === 'published' || $exam->status?->value === 'completed' ? ' · Published' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('exam_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="student_id">Student</label>
                    <select id="student_id" name="student_id" class="form-select" required>
                        <option value="">Select student…</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                {{ $student->full_name }} · {{ $student->student_number }}@if ($student->classRoom?->name) · {{ $student->classRoom->name }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Generate report card') }}
                    </button>
                    <a href="{{ route('report-cards.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
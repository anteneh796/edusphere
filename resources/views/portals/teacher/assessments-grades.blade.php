<x-layouts.app :title="__('Grades')">
    <x-page-header
        :title="$assessment->title"
        :description="$assessment->classSubject?->subject?->name ?? __('Assessment') . ' · ' . ($assessment->classRoom?->name ?? '—')" />

    @php($resultMap = $results->keyBy('student_id'))

    <x-card
        :title="__('Record grades')"
        :subtitle="__('Total marks: :marks', ['marks' => $assessment->total_marks])">
        @if ($students->isEmpty())
            <x-empty-state icon="users" :title="__('No students')" :message="__('Enrolled students will appear here.')" />
        @else
            <form method="POST" action="{{ route('cms.teacher.assessments.grades.store', $assessment) }}">
                @csrf

                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Student') }}</th>
                            <th>{{ __('Admission #') }}</th>
                            <th>{{ __('Marks') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            @php($result = $resultMap->get($student->getKey()))
                            <tr>
                                <td>{{ $student->full_name }}</td>
                                <td>{{ $student->student_number }}</td>
                                <td>
                                    <input type="hidden" name="student_id[]" value="{{ $student->getKey() }}" />
                                    <input
                                        type="number"
                                        name="marks_obtained[{{ $student->getKey() }}]"
                                        class="form-control"
                                        style="max-width: 8rem;"
                                        min="0"
                                        :max="$assessment->total_marks"
                                        value="{{ old('marks_obtained.'.$student->getKey(), $result?->marks_obtained) }}" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">{{ __('Save grades') }}</button>
                    <a href="{{ route('cms.teacher.assessments') }}" class="btn btn-ghost">{{ __('Back') }}</a>
                </div>
            </form>
        @endif
    </x-card>
</x-layouts.app>
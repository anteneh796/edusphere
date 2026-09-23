<x-layouts.app :title="__('Class')">
    <x-page-header :title="$classSubject->subject?->name ?? __('Class')" :description="$classSubject->classRoom?->name ?? '—'">
        <a href="{{ route('cms.teacher.attendance') }}" class="btn btn-ghost">{{ __('Attendance') }}</a>
    </x-page-header>

    <x-card :title="__('Students enrolled')">
        @if ($classSubject->classRoom->students->isEmpty())
            <x-empty-state icon="users" :title="__('No students yet')" :message="__('Enrolled students will appear here.')" />
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('Admission #') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($classSubject->classRoom->students as $student)
                        <tr>
                            <td>{{ $student->full_name }}</td>
                            <td>{{ $student->student_number }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-card>
</x-layouts.app>

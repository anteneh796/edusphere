<x-layouts.app :title="__('Homeroom')">
    <x-page-header :title="__('Homeroom')" :description="__('Students in your homeroom class.')" />

    @if (! $homeroom)
        <x-empty-state icon="home" :title="__('No homeroom assigned')" :message="__('Your homeroom class will appear here once assigned.')" />
    @else
        <div class="grid grid-stats">
            <x-stat-card :label="__('Homeroom class')" :value="$homeroom->classRoom?->name ?? '—'" icon="home" color="primary" />
            <x-stat-card :label="__('Students')" :value="$students->count()" icon="users" color="success" />
            <x-stat-card :label="__('Attendance sessions')" :value="$sessionCount" icon="clipboard-check" color="warning" />
        </div>

        <x-card :title="__('Homeroom students')" subtitle="{{ $homeroom->classRoom?->gradeLevel?->name ?? '' }}">
            @if ($students->isEmpty())
                <x-empty-state icon="users" :title="__('No students yet')" :message="__('Enrolled students will appear here.')" />
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Student') }}</th>
                                <th>{{ __('Student no.') }}</th>
                                <th>{{ __('Class') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $student)
                                <tr>
                                    <td>{{ $student->full_name }}</td>
                                    <td>{{ $student->student_number }}</td>
                                    <td>{{ $student->classRoom?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    @endif
</x-layouts.app>
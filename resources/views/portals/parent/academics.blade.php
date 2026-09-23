<x-layouts.app :title="__('Academics')">
    <x-page-header
        :title="__('Academics')"
        :description="$ward ? __('Grades for :name', ['name' => $ward->full_name]) : __('Academics')">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    @if (! $ward)
        <x-card>
            <x-empty-state icon="users" :title="__('No child selected')" :message="__('Link or select a child to view grades.')" />
        </x-card>
    @else
        <div class="grid gap-4">
            @forelse ($bySubject as $subjectName => $grades)
                <x-card :title="$subjectName ?: __('General')">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Assessment') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th class="text-right">{{ __('Marks') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grades as $grade)
                                <tr>
                                    <td>{{ $grade->assessment?->title ?? $grade->examSubject?->subject?->name }}</td>
                                    <td>{{ $grade->assessment ? __('Classroom') : __('Exam') }}</td>
                                    <td class="text-right">
                                        {{ $grade->marks_obtained ?? '—' }}
                                        @if ($grade->assessment?->total_marks)
                                            / {{ $grade->assessment->total_marks }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-card>
            @empty
                <x-card>
                    <x-empty-state icon="award" :title="__('No published grades')" :message="__('Blended grades are shown once the school publishes them.')" />
                </x-card>
            @endforelse
        </div>

        <p class="text-xs text-light mt-3">
            {{ __('Showing classroom assessments and exam results that the school has published. Contact the school for questions about a specific grade.') }}
        </p>
    @endif
</x-layouts.app>
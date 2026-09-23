<x-layouts.app :title="__('My results')">
    <x-page-header
        :title="__('My exam results')"
        :description="__('Your published exam results, most recent first.')">
        <a href="{{ route('cms.student.dashboard') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Dashboard') }}
        </a>
    </x-page-header>

    <x-card :title="__('Results')">
        @forelse ($results as $result)
            <div class="list-row">
                <x-icon name="award" class="icon-md text-light" />
                <div class="text-sm">
                    {{ $result->examSubject?->subject?->name }}
                    <div class="text-xs text-light">
                        {{ $result->examSubject?->exam?->name }} ·
                        {{ $result->examSubject?->classRoom?->name }}
                    </div>
                </div>
                <div>
                    <span class="text-sm-semibold">{{ $result->marks_obtained ?? $result->score_obtained ?? '—' }}</span>
                    <span class="text-xs text-light"> / {{ $result->max_marks ?? $result->examSubject?->max_marks ?? '—' }}</span>
                </div>
            </div>
        @empty
            <x-empty-state icon="award" :title="__('No results published yet')" :message="__('Your results will appear here once your exams have been published.')" />
        @endforelse
    </x-card>
</x-layouts.app>

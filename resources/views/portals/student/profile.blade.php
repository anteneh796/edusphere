<x-layouts.app :title="__('My profile')">
    <x-page-header :title="__('My profile')" :description="__('Your personal and enrollment details as recorded by the school.')">
        <a href="{{ route('cms.student.dashboard') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Dashboard') }}
        </a>
    </x-page-header>

    <x-card :title="__('Identity')">
        <div class="profile-head">
            <x-avatar :initials="Str::of(($student->first_name ?? 'S'))->substr(0, 1)->append(Str::of(($student->last_name ?? 'T'))->substr(0, 1))" size="lg" />
            <div class="profile-head-body">
                <div class="text-lg-semibold">{{ $student?->full_name ?? __('Student') }}</div>
                <div class="text-xs text-light">{{ $student?->student_number ?? '—' }}</div>
            </div>
        </div>

        <dl class="detail-grid">
            <div class="detail-row">
                <dt>{{ __('First name') }}</dt>
                <dd>{{ $student?->first_name ?? '—' }}</dd>
            </div>
            <div class="detail-row">
                <dt>{{ __('Last name') }}</dt>
                <dd>{{ $student?->last_name ?? '—' }}</dd>
            </div>
            <div class="detail-row">
                <dt>{{ __('Other names') }}</dt>
                <dd>{{ $student?->other_names ?? '—' }}</dd>
            </div>
            <div class="detail-row">
                <dt>{{ __('Gender') }}</dt>
                <dd>{{ ucfirst((string) ($student?->gender ?? '—')) }}</dd>
            </div>
            <div class="detail-row">
                <dt>{{ __('Date of birth') }}</dt>
                <dd>{{ $student?->date_of_birth?->format('M j, Y') ?? '—' }}</dd>
            </div>
            <div class="detail-row">
                <dt>{{ __('Student number') }}</dt>
                <dd>{{ $student?->student_number ?? '—' }}</dd>
            </div>
            <div class="detail-row">
                <dt>{{ __('Class') }}</dt>
                <dd>{{ $student?->classRoom?->name ?? '—' }}</dd>
            </div>
            <div class="detail-row">
                <dt>{{ __('Enrolled date') }}</dt>
                <dd>{{ $student?->enrollment_date?->format('M j, Y') ?? '—' }}</dd>
            </div>
        </dl>
    </x-card>
</x-layouts.app>

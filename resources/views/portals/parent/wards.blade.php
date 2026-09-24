<x-layouts.app :title="__('My children')">
    <x-page-header
        :title="__('My children')"
        :description="__('Students linked to your guardian profile.')" />

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        @forelse ($wards as $w)
            <x-card :title="$w->full_name" hover>
                <div class="flex items-start gap-3">
                    <x-avatar :initials="$w->initials()" />
                    <div class="min-w-0 flex-1">
                        <div class="truncate">{{ $w->full_name }}</div>
                        <div class="text-xs text-light">
                            {{ $w->student_number }}
                            @if ($w->gradeLevel?->name) · {{ $w->gradeLevel->name }} @endif
                            @if ($w->classRoom?->name) · {{ $w->classRoom->name }} @endif
                        </div>
                        <div class="text-xs text-light mt-1">
                            {{ $w->statusLabel() }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('cms.parent.wards.show', $w) }}" class="btn btn-primary btn-sm">
                        {{ __('View profile') }}
                    </a>
                    <a href="{{ route('cms.parent.attendance') }}" class="btn btn-ghost btn-sm">{{ __('Attendance') }}</a>
                    <a href="{{ route('cms.parent.academics') }}" class="btn btn-ghost btn-sm">{{ __('Grades') }}</a>
                </div>
            </x-card>
        @empty
            <x-card>
                <x-empty-state
                    icon="users"
                    :title="__('No children linked')"
                    :message="__('Once the registrar links your guardian profile, your children will be listed here.')" />
            </x-card>
        @endforelse
    </div>
</x-layouts.app>
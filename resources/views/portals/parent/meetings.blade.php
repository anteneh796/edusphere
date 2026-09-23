<x-layouts.app :title="__('Meeting requests')">
    <x-page-header
        :title="__('Meeting requests')"
        :description="__('Book a parent-teacher meeting for your child.')">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    @php
        $meetingTypeOptions = collect(\App\Support\Enums\MeetingRequestType::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()]);
    @endphp

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3" style="align-items:start;">
        <x-card :title="__('Request a meeting')">
            @if ($wards->isEmpty() || $teachers->isEmpty())
                <x-empty-state icon="calendar" :title="__('Not available')" :message="__('A linked child and a teacher in their class are needed to request a meeting.')" />
            @else
                <form method="POST" action="{{ route('cms.parent.meetings.store') }}" class="grid gap-4">
                    @csrf

                    <x-select
                        name="student"
                        :label="__('Child')"
                        :options="$wards->mapWithKeys(fn ($w) => [$w->getKey() => $w->full_name.' ('.$w->student_number.')'])"
                        :value="old('student', $ward?->getKey())"
                        placeholder="{{ __('Select a child…') }}"
                        required />

                    <x-select
                        name="teacher_id"
                        :label="__('Teacher')"
                        :options="$teachers->mapWithKeys(fn ($t) => [$t->getKey() => $t->full_name])"
                        placeholder="{{ __('Select a teacher…') }}"
                        required />

                    <x-select
                        name="meeting_type"
                        :label="__('Meeting type')"
                        :options="$meetingTypeOptions"
                        placeholder="{{ __('Select a type…') }}"
                        required />

                    <div class="grid grid-2 gap-4">
                        <x-input name="preferred_date" type="date" :label="__('Preferred date')" :value="old('preferred_date')" required />
                        <x-input name="preferred_time" type="time" :label="__('Preferred time')" :value="old('preferred_time')" />
                    </div>

                    <x-input name="reason" :label="__('Reason (optional)')" :value="old('reason')" />

                    <div>
                        <button type="submit" class="btn btn-primary">{{ __('Request meeting') }}</button>
                    </div>
                </form>
            @endif
        </x-card>

        <x-card :title="__('Your meetings')" style="grid-column: span 2;">
            @forelse ($requests as $request)
                <div class="list-row">
                    <x-avatar :initials="$request->student?->initials() ?? '–'" />
                    <div class="min-w-0 flex-1">
                        <div class="truncate">
                            {{ $request->student?->full_name ?? '—' }} · {{ $request->teacher?->full_name ?? '—' }}
                        </div>
                        <div class="text-xs text-light">
                            {{ $request->meeting_type?->label() ?? ucfirst($request->meeting_type ?? '') }}
                            · {{ $request->preferred_date?->format('d M Y') ?? '—' }}
                            @if ($request->preferred_time) · {{ $request->preferred_time }} @endif
                        </div>
                        @if ($request->reason)
                            <div class="text-xs text-light">{{ $request->reason }}</div>
                        @endif
                        @if ($request->staff_note)
                            <div class="text-xs text-light mt-1">{{ __('School note:') }} {{ $request->staff_note }}</div>
                        @endif
                    </div>
                    <span class="badge badge-{{ $request->status?->badgeColor() ?? 'neutral' }}">{{ $request->status?->label() ?? ucfirst($request->status ?? '') }}</span>
                    @if ($request->status?->value === 'requested')
                        <form method="POST" action="{{ route('cms.parent.meetings.cancel', $request) }}" onsubmit="return confirm('{{ __('Cancel this meeting?') }}')">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm">
                                <x-icon name="x" class="icon-sm" />
                                {{ __('Cancel') }}
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <x-empty-state icon="calendar" :title="__('No meetings yet')" :message="__('Meeting requests and their status will appear here.')" />
            @endforelse
        </x-card>
    </div>
</x-layouts.app>
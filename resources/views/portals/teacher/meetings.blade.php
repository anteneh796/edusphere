<x-layouts.app :title="__('Meetings')">
    <x-page-header
        :title="__('Meeting requests')"
        :description="__('Parent-teacher meeting requests for your classes.')" />

    <div class="grid gap-4">
        @forelse ($meetings as $meeting)
            <x-card :title="__(':student — :type', ['student' => $meeting->student?->full_name ?? '—', 'type' => $meeting->meeting_type?->label() ?? ucfirst($meeting->meeting_type ?? '')])" :subtitle="$meeting->student?->classRoom?->gradeLevel?->name ?? ''">
                <div class="list-row">
                    <x-avatar :initials="$meeting->guardian?->initials() ?? '–'" />
                    <div class="min-w-0 flex-1">
                        <div class="text-sm">{{ $meeting->guardian?->full_name ?? '—' }}</div>
                        <div class="text-xs text-light">
                            {{ __('Preferred: :date', ['date' => $meeting->preferred_date?->format('d M Y') ?? '—']) }}
                            @if ($meeting->preferred_time) · {{ $meeting->preferred_time }} @endif
                            @if ($meeting->reason) · {{ $meeting->reason }} @endif
                        </div>
                        @if ($meeting->staff_note)
                            <div class="text-xs text-light mt-1">{{ __('Note:') }} {{ $meeting->staff_note }}</div>
                        @endif
                    </div>
                    <span class="badge badge-{{ $meeting->status?->badgeColor() ?? 'neutral' }}">{{ $meeting->status?->label() ?? ucfirst($meeting->status ?? '') }}</span>
                </div>

                @if (in_array($meeting->status?->value ?? null, ['requested', 'confirmed'], true))
                    <details class="mt-3">
                        <summary class="text-sm cursor-pointer text-primary">{{ __('Update meeting') }}</summary>
                        <div class="mt-2">
                            <form method="POST" action="{{ route('cms.teacher.meetings.review', $meeting) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <x-input name="staff_note" :label="__('Note')" placeholder="{{ __('Optional note for the parent') }}" />
                                <input type="hidden" name="status" value="{{ \App\Support\Enums\MeetingRequestStatus::Confirmed->value }}">
                                <button type="submit" class="btn btn-success btn-sm">{{ __('Confirm') }}</button>
                            </form>
                            <div class="flex flex-wrap items-end gap-3 mt-2">
                                <form method="POST" action="{{ route('cms.teacher.meetings.review', $meeting) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ \App\Support\Enums\MeetingRequestStatus::Completed->value }}">
                                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Mark completed') }}</button>
                                </form>
                                <form method="POST" action="{{ route('cms.teacher.meetings.review', $meeting) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ \App\Support\Enums\MeetingRequestStatus::Cancelled->value }}">
                                    <button type="submit" class="btn btn-ghost btn-sm">{{ __('Cancel') }}</button>
                                </form>
                            </div>
                        </div>
                    </details>
                @endif
            </x-card>
        @empty
            <x-card>
                <x-empty-state icon="calendar" :title="__('No meeting requests')" :message="__('Parent-teacher meeting requests will appear here.')" />
            </x-card>
        @endforelse
    </div>
</x-layouts.app>
<x-layouts.app :title="__('Absence requests')">
    <x-page-header
        :title="__('Parent service: absence requests')"
        :description="__('Review absence explanations submitted by parents.')" />

    <div class="grid gap-4">
        @forelse ($absences as $absence)
            <x-card :title="$absence->student?->full_name ?? '—'" :subtitle="$absence->student?->classRoom?->gradeLevel?->name ?? ''">
                <div class="list-row">
                    <x-avatar :initials="$absence->parent?->initials() ?? '–'" />
                    <div class="min-w-0 flex-1">
                        <div class="text-sm">
                            {{ $absence->parent?->full_name ?? '—' }}
                            <span class="text-light">· {{ $absence->student?->student_number ?? '—' }}</span>
                        </div>
                        <div class="text-xs text-light">
                            {{ __('Absent on :date', ['date' => $absence->absence_date?->format('d M Y') ?? '—']) }}
                            · {{ $absence->reason }}
                        </div>
                        @if ($absence->reviewer_note)
                            <div class="text-xs text-light mt-1">{{ __('Note:') }} {{ $absence->reviewer_note }}</div>
                        @endif
                    </div>
                    <span class="badge badge-{{ $absence->status?->badgeColor() ?? 'neutral' }}">{{ $absence->status?->label() ?? ucfirst($absence->status ?? '') }}</span>
                </div>

                @if (in_array($absence->status?->value ?? null, ['submitted', 'under_review'], true))
                    <details class="mt-3">
                        <summary class="text-sm cursor-pointer text-primary">{{ __('Review request') }}</summary>
                        <div class="flex flex-wrap items-end gap-3 mt-2">
                            <form method="POST" action="{{ route('parent-services.absences.review', $absence) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <input type="hidden" name="status" value="{{ \App\Support\Enums\AbsenceRequestStatus::Approved->value }}">
                                <button type="submit" class="btn btn-success btn-sm">{{ __('Approve') }}</button>
                            </form>
                            <form method="POST" action="{{ route('parent-services.absences.review', $absence) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <input type="hidden" name="status" value="{{ \App\Support\Enums\AbsenceRequestStatus::Rejected->value }}">
                                <input type="text" name="reviewer_note" class="form-control" placeholder="{{ __('Reason (optional)') }}" style="max-width: 260px;">
                                <button type="submit" class="btn btn-danger btn-sm">{{ __('Reject') }}</button>
                            </form>
                        </div>
                    </details>
                @endif
            </x-card>
        @empty
            <x-card>
                <x-empty-state icon="clipboard-check" :title="__('No absence requests')" :message="__('Parent absence explanations will appear here.')" />
            </x-card>
        @endforelse
    </div>
</x-layouts.app>
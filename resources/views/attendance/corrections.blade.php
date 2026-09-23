<x-layouts.app :title="__('Attendance corrections')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Corrections')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.corrections'])

    <x-page-header :title="__('Attendance corrections')"
        :description="__('Review and apply correction requests submitted on locked sessions')">
        <x-badge color="warning" :dot="true">{{ __(':count pending', ['count' => $pending]) }}</x-badge>
    </x-page-header>

    <x-card class="mt-4">
        <form method="GET" class="flex" style="gap: var(--space-2); flex-wrap: wrap; align-items: end;">
            <div>
                <label class="text-xs text-muted">{{ __('Status') }}</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">{{ __('All') }}</option>
                    @foreach (\App\Support\Enums\AttendanceCorrectionStatus::cases() as $option)
                        <option value="{{ $option->value }}" @selected(request('status') === $option->value)>{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
        </form>
    </x-card>

    <div style="display:flex; flex-direction:column; gap: var(--space-3); margin-top: var(--space-4);">
        @forelse ($rows as $correction)
            <x-card
                :title="($correction->record?->student?->full_name ?? __('Unknown student')).' — '.($correction->record?->session?->classRoom?->name ?? '')"
                :subtitle="'Marked '.($correction->record?->session?->date?->format('D, M j, Y') ?? '—').' · '.($correction->old_status?->label() ?? '—').' → '.($correction->requested_status?->label() ?? '—')">
                <x-slot:actions>
                    <x-badge :color="$correction->status?->badgeColor()" :dot="true">{{ $correction->status?->label() }}</x-badge>
                </x-slot:actions>

                <p class="text-sm mt-1">{{ __('Reason') }}: {{ $correction->reason }}</p>

                <dl class="detail-list mt-2" style="max-width: 480px;">
                    <div>
                        <dt>{{ __('Requested by') }}</dt>
                        <dd>{{ $correction->requestedBy?->full_name ?? '—' }} · {{ $correction->submitted_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                    </div>
                    @if ($correction->reviewedBy)
                        <div>
                            <dt>{{ __('Reviewed by') }}</dt>
                            <dd>{{ $correction->reviewedBy?->full_name ?? '—' }} · {{ $correction->reviewed_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                    @endif
                </dl>

                @if ($correction->isPending())
                    <div class="flex mt-3" style="gap: var(--space-2); flex-wrap: wrap;">
                        <form method="POST" action="{{ route('attendance.corrections.review', $correction) }}">
                            @csrf
                            <input type="hidden" name="decision" value="approve" />
                            <input type="text" name="reviewer_note" class="form-control" placeholder="{{ __('Optional note') }}" style="max-width: 220px;" />
                            <button type="submit" class="btn btn-sm btn-primary">
                                <x-icon name="check" class="icon-sm" />
                                {{ __('Approve & apply') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('attendance.corrections.review', $correction) }}">
                            @csrf
                            <input type="hidden" name="decision" value="reject" />
                            <input type="text" name="reviewer_note" class="form-control" placeholder="{{ __('Reason for rejection') }}" style="max-width: 220px;" />
                            <button type="submit" class="btn btn-sm btn-secondary">
                                <x-icon name="x" class="icon-sm" />
                                {{ __('Reject') }}
                            </button>
                        </form>
                    </div>
                @elseif ($correction->reviewer_note)
                    <p class="text-sm text-muted mt-2">{{ __('Reviewer note') }}: {{ $correction->reviewer_note }}</p>
                @endif
            </x-card>
        @empty
            <x-card>
                <x-empty-state icon="check-circle" :title="__('Nothing to review')" :message="__('No correction requests match the current filter.')" />
            </x-card>
        @endforelse
    </div>
</x-layouts.app>
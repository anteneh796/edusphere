<x-layouts.app :title="__('Approval request')">
    <x-breadcrumb :items="[
        ['label' => __('Approvals'), 'url' => route('approvals.index')],
        ['label' => $approval->typeLabel()],
    ]" />

    <x-page-header :title="$approval->typeLabel()" :description="__('Submitted :date', ['date' => $approval->submitted_at?->format('M j, Y H:i')])">
        <x-badge :color="$approval->statusBadgeColor()" :dot="$approval->isPending()">
            {{ $approval->statusEnum()?->label() ?? $approval->status }}
        </x-badge>
    </x-page-header>

    <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-3);">
        <x-card :title="__('Details')">
            <dl class="detail-list">
                <dt>{{ __('Requested by') }}</dt>
                <dd>{{ $approval->requester?->full_name ?? '—' }}</dd>
                <dt>{{ __('Reviewer') }}</dt>
                <dd>
                    @if ($approval->reviewed_by_id)
                        {{ $approval->reviewer?->full_name }}
                    @else
                        {{ $approval->reviewerTitle() }}
                    @endif
                </dd>
                <dt>{{ __('Submitted') }}</dt>
                <dd>{{ $approval->submitted_at?->format('M j, Y H:i') ?? '—' }}</dd>
                @if ($approval->reviewed_at)
                    <dt>{{ __('Reviewed') }}</dt>
                    <dd>{{ $approval->reviewed_at->format('M j, Y H:i') }}</dd>
                @endif
                <dt>{{ __('Reason') }}</dt>
                <dd>{{ $approval->reason ?: '—' }}</dd>

                @if ($subject)
                    <dt>{{ __('Student') }}</dt>
                    <dd>
                        <a href="{{ route('students.show', $subject) }}">{{ $subject->full_name ?? $subject->first_name }}</a>
                        <div class="text-xs text-muted">{{ $subject->student_number }}</div>
                    </dd>
                    <dt>{{ __('Grade') }}</dt>
                    <dd>{{ $subject->gradeLevel?->name ?? '—' }}</dd>
                @endif

                @if (! empty($approval->data['target_grade_id']))
                    <dt>{{ __('Target grade') }}</dt>
                    <dd>{{ \App\Domains\Academics\Models\GradeLevel::find($approval->data['target_grade_id'])?->name ?? '—' }}</dd>
                @endif
                @if (! empty($approval->data['target_class_id']))
                    <dt>{{ __('Target class') }}</dt>
                    <dd>{{ \App\Domains\Academics\Models\ClassRoom::find($approval->data['target_class_id'])?->name ?? '—' }}</dd>
                @endif
                @if (! empty($approval->data['leave_start']))
                    <dt>{{ __('Leave period') }}</dt>
                    <dd>{{ $approval->data['leave_start'] }} → {{ $approval->data['leave_end'] ?? '…' }}</dd>
                @endif
                @if (! empty($approval->data['waiver_amount']))
                    <dt>{{ __('Waiver amount') }}</dt>
                    <dd>{{ number_format((float) $approval->data['waiver_amount'], 2) }}</dd>
                @endif
            </dl>
        </x-card>

        <div class="flex flex-col" style="gap: var(--space-3);">
            @if ($approval->isPending() && auth()->user()->hasAnyPermission(['approvals.approve']))
                <x-card :title="__('Decision')">
                    <form method="POST" action="{{ route('approvals.review', $approval) }}" novalidate>
                        @csrf
                        <div class="form-group">
                            <label class="form-label" for="reviewer_note">{{ __('Reviewer note') }}</label>
                            <textarea id="reviewer_note" name="reviewer_note" rows="3" class="form-input" placeholder="{{ __('Optional note for the requester') }}">{{ old('reviewer_note') }}</textarea>
                        </div>
                        <div class="flex gap-1">
                            <button type="submit" name="action" value="approve" class="btn btn-success">
                                <x-icon name="check" class="icon-sm" />
                                {{ __('Approve') }}
                            </button>
                            <button type="submit" name="action" value="deny" class="btn btn-danger"
                                onclick="return confirm('{{ __('Deny this request?') }}');">
                                <x-icon name="x" class="icon-sm" />
                                {{ __('Deny') }}
                            </button>
                        </div>
                    </form>
                </x-card>
            @endif

            @if ($approval->reviewer_note)
                <x-card :title="__('Reviewer note')">
                    <p class="text-sm">{{ $approval->reviewer_note }}</p>
                    <p class="text-xs text-muted">{{ __('Reviewed by :name', ['name' => $approval->reviewer?->full_name]) }}</p>
                </x-card>
            @endif

            <x-card :title="__('Timeline')">
                <div class="text-sm text-muted">
                    <div>{{ __('Submitted :date', ['date' => $approval->submitted_at?->format('M j, Y H:i')]) }}</div>
                    @if ($approval->reviewed_at)
                        <div>{{ __('Reviewed :date', ['date' => $approval->reviewed_at->format('M j, Y H:i')]) }}</div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
<x-layouts.app :title="__('Leave Request')">

    <x-page-header :title="__('Leave Request')"
        :description="__(':type · :employee', ['type' => $request->leaveType?->name ?? __('Leave'), 'employee' => $request->employee->full_name])">
        <a href="{{ route('hr.leave.index') }}" class="btn btn-secondary">{{ __('Back to leave requests') }}</a>
    </x-page-header>

    <div class="grid" style="grid-template-columns: 2fr 1fr; gap: var(--space-3);">
        <x-card :title="__('Request details')">
            <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                <div class="text-sm"><span class="text-muted">{{ __('Employee') }}:</span>
                    <a href="{{ route('hr.employees.show', $request->employee) }}" class="link">{{ $request->employee->full_name }}</a>
                    ({{ $request->employee->employee_id }})
                </div>
                <div class="text-sm"><span class="text-muted">{{ __('Leave type') }}:</span> {{ $request->leaveType?->name }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Start date') }}:</span> {{ $request->start_date->format('M j, Y') }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('End date') }}:</span> {{ $request->end_date->format('M j, Y') }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Days') }}:</span> {{ $request->days }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Status') }}:</span>
                    <x-badge :color="$request->statusBadgeColor()" :dot="true">{{ $request->statusLabel() }}</x-badge>
                </div>
            </div>

            @if ($request->reason)
                <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
                <div class="text-sm"><span class="text-muted">{{ __('Reason') }}:</span><br />{{ $request->reason }}</div>
            @endif

            @if ($request->submitted_at)
                <div class="text-xs text-muted" style="margin-top: var(--space-2);">
                    {{ __('Submitted') }} {{ $request->submitted_at->format('M j, Y H:i') }}
                    @if ($request->submittedBy)
                        {{ __('by') }} {{ $request->submittedBy->first_name }} {{ $request->submittedBy->last_name }}
                    @endif
                </div>
            @endif
            @if ($request->reviewed_at && $request->reviewedBy)
                <div class="text-xs text-muted">
                    {{ __('Reviewed') }} {{ $request->reviewed_at->format('M j, Y H:i') }} {{ __('by') }} {{ $request->reviewedBy->first_name }} {{ $request->reviewedBy->last_name }}
                </div>
            @endif
            @if ($request->reviewed_note)
                <div class="text-sm" style="margin-top: var(--space-1);">
                    <span class="text-muted">{{ __('Reviewer note') }}:</span> {{ $request->reviewed_note }}
                </div>
            @endif
        </x-card>

        <div class="flex flex-col" style="gap: var(--space-3);">
            @if ($canReview && $request->isOpen())
                <x-card :title="__('Review request')">
                    <div class="flex flex-col" style="gap: var(--space-2);">
                        <form method="POST" action="{{ route('hr.leave.review', $request) }}" class="flex flex-col" style="gap: var(--space-2);">
                            @csrf
                            <input type="hidden" name="action" value="approved" />
                            <x-textarea name="note" label="{{ __('Note') }}" :rows="3" placeholder="{{ __('Optional note to the employee…') }}">{{ old('note') }}</x-textarea>
                            <button type="submit" class="btn btn-success">{{ __('Approve') }}</button>
                        </form>
                        <form method="POST" action="{{ route('hr.leave.review', $request) }}" class="flex flex-col" style="gap: var(--space-2);">
                            @csrf
                            <input type="hidden" name="action" value="rejected" />
                            <x-textarea name="note" label="{{ __('Rejection reason') }}" :rows="3" placeholder="{{ __('Required for clarity…') }}">{{ old('note') }}</x-textarea>
                            <button type="submit" class="btn btn-danger">{{ __('Reject') }}</button>
                        </form>
                    </div>
                </x-card>
            @endif

            @if ($request->isOpen() && (auth()->user()->is($request->employee?->user) || $canReview))
                <x-card>
                    <form method="POST" action="{{ route('hr.leave.cancel', $request) }}" onsubmit="return confirm('{{ __('Cancel this leave request?') }}')">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-block">{{ __('Cancel request') }}</button>
                    </form>
                </x-card>
            @endif
        </div>
    </div>

</x-layouts.app>
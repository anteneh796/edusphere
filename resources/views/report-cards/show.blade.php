<x-layouts.app :title="__('Report card #:number', ['number' => $card->report_card_number ?? $card->id])">
    @php
        $canEdit = auth()->user()?->hasPermission('exams.edit');
        $canPublish = auth()->user()?->hasPermission('exams.publish');
    @endphp

    <x-breadcrumb :items="[
        ['label' => __('Report Cards'), 'url' => route('report-cards.index')],
        ['label' => $card->report_card_number ?? $card->id],
    ]" />

    <x-page-header :title="__('Report card :number', ['number' => $card->report_card_number ?? $card->id])"
        :description="$card->student?->full_name.' · '.($card->student?->student_number ?? '—').' · '.($card->exam?->name ?? '—')">
        <span style="display:inline-flex; align-items:center; gap: var(--space-2); flex-wrap:wrap;">
            <x-badge :color="$card->status?->badgeColor()" :dot="true">{{ $card->status?->label() }}</x-badge>

            @if ($canEdit && $card->status === \App\Support\Enums\ReportCardStatus::Generated)
                <form method="POST" action="{{ route('report-cards.approve', $card) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <x-icon name="check-circle" class="icon-sm" />
                        {{ __('Approve') }}
                    </button>
                </form>
            @endif

            @if ($canPublish && $card->status === \App\Support\Enums\ReportCardStatus::Approved)
                <form method="POST" action="{{ route('report-cards.publish', $card) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <x-icon name="layers" class="icon-sm" />
                        {{ __('Publish') }}
                    </button>
                </form>
            @endif

            <a href="{{ route('report-cards.index') }}" class="btn btn-secondary">
                <x-icon name="chevron-left" class="icon-sm" />
                {{ __('Back to list') }}
            </a>
        </span>
    </x-page-header>

    @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom: var(--space-3);">
            <x-icon name="alert-triangle" class="icon-sm" />
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cards" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <x-stat-card :label="__('Total obtained')" :value="$card->total_obtained_marks === null ? '—' : number_format((float) $card->total_obtained_marks, 2).' / '.number_format((float) $card->total_max_marks, 2)" icon="clipboard-check" color="primary" />
        <x-stat-card :label="__('Average')" :value="$card->average_percent === null ? '—' : number_format((float) $card->average_percent, 2).'%'" icon="bar-chart" color="info" />
        <x-stat-card :label="__('Class rank')" :value="$card->class_rank && $card->class_size ? $card->class_rank.' / '.$card->class_size : '—'" icon="trophy" color="warning" />
    </div>

    @if ($card->attendance_total > 0)
        <div class="grid grid-cards" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-top: var(--space-3);">
            <x-stat-card :label="__('Attendance present')" :value="$card->attendance_present" icon="check-circle" color="success" />
            <x-stat-card :label="__('Attendance absent')" :value="$card->attendance_absent" icon="alert-circle" color="danger" />
            <x-stat-card :label="__('Attendance total')" :value="$card->attendance_total" icon="calendar" color="neutral" />
        </div>
    @endif

    <x-card :title="__('Results')" class="mt-4">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Subject') }}</th>
                        <th class="text-right">{{ __('Max') }}</th>
                        <th class="text-right">{{ __('Obtained') }}</th>
                        <th class="text-right">{{ __('Percentage') }}</th>
                        <th>{{ __('Teacher comment') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($card->items->sortBy('position') as $item)
                        <tr>
                            <td class="text-sm text-muted">{{ $loop->iteration }}</td>
                            <td class="text-sm" style="font-weight: var(--weight-semibold);">{{ $item->subject?->name ?? '—' }}</td>
                            <td class="text-sm text-right">{{ number_format((float) $item->max_marks, 2) }}</td>
                            <td class="text-sm text-right">{{ $item->marks_obtained === null ? '—' : number_format((float) $item->marks_obtained, 2) }}</td>
                            <td class="text-sm text-right">{{ $item->percentage === null ? '—' : number_format((float) $item->percentage, 1).'%' }}</td>
                            <td class="text-sm">{{ $item->teacher_comment ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="clipboard-check" :title="__('No results on this card')" :message="__('Results are added when exam marks are entered and the card is generated.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <div style="margin-top: var(--space-4); display:flex; flex-direction:column; gap: var(--space-4);">
        <x-card :title="__('Comments & remarks')">
            <dl class="detail-list">
                <div>
                    <dt>{{ __('Teacher comment') }}</dt>
                    <dd>{{ $card->teacher_comment ?? '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('Principal remarks') }}</dt>
                    <dd>{{ $card->principal_remarks ?? '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('Promotion status') }}</dt>
                    <dd>{{ $card->promotion_status ?? '—' }}</dd>
                </div>
            </dl>

            @if ($card->comments->isNotEmpty())
                <div style="margin-top: var(--space-3); display:flex; flex-direction:column; gap: var(--space-2);">
                    @foreach ($card->comments as $comment)
                        <div class="card-subtitle mt-1">
                            <x-badge :color="$comment->type?->badgeColor()">{{ $comment->type?->label() }}</x-badge>
                            <span class="text-sm">{{ $comment->comment }}</span>
                            <span class="text-xs text-muted">— {{ $comment->commentedBy?->full_name ?? __('Automated') }} · {{ $comment->position }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card :title="__('Workflow')">
            <dl class="detail-list">
                <div>
                    <dt>{{ __('Generated by') }}</dt>
                    <dd>{{ $card->generatedBy?->full_name ?? '—' }}@if ($card->created_at) · {{ $card->created_at->format('M j, Y H:i') }}@endif</dd>
                </div>
                <div>
                    <dt>{{ __('Approved by') }}</dt>
                    <dd>{{ $card->approvedBy?->full_name ?? '—' }}@if ($card->approved_at) · {{ $card->approved_at->format('M j, Y H:i') }}@endif</dd>
                </div>
                <div>
                    <dt>{{ __('Published by') }}</dt>
                    <dd>{{ $card->publishedBy?->full_name ?? '—' }}@if ($card->published_at) · {{ $card->published_at->format('M j, Y H:i') }}@endif</dd>
                </div>
            </dl>
        </x-card>
    </div>
</x-layouts.app>
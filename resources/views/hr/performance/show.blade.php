<x-layouts.app :title="__('Performance Review')">

    <x-page-header :title="__('Performance Review')"
        :description="__(':period · :employee', ['period' => $review->period, 'employee' => $review->employee->full_name])">
        <div class="flex gap-1">
            @if (auth()->user()->hasPermission('hr.edit') && $review->status !== \App\Support\Enums\PerformanceReviewStatus::Archived->value)
                <a href="{{ route('hr.performance.edit', $review) }}" class="btn btn-secondary">
                    <x-icon name="pencil" class="icon-sm" />
                    {{ __('Edit') }}
                </a>
                @if ($review->status === \App\Support\Enums\PerformanceReviewStatus::Completed->value)
                    <form method="POST" action="{{ route('hr.performance.status', $review) }}" class="inline">
                        @csrf
                        <button type="submit" name="status" value="archived" class="btn btn-ghost" onclick="return confirm('{{ __('Archive this review?') }}')">{{ __('Archive') }}</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('hr.performance.status', $review) }}" class="inline">
                        @csrf
                        <button type="submit" name="status" value="completed" class="btn btn-ghost">{{ __('Mark completed') }}</button>
                    </form>
                @endif
            @endif
        </div>
    </x-page-header>

    <div class="grid" style="grid-template-columns: 2fr 1fr; gap: var(--space-3);">
        <div class="flex flex-col" style="gap: var(--space-3);">
            <x-card :title="__('Scores')">
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            @foreach (\App\Domains\HumanResources\Controllers\PerformanceReviewController::CATEGORIES as $key => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-right" style="width:40%;">
                                        <div style="display:flex; align-items:center; gap: var(--space-2);">
                                            <div style="flex:1; height:8px; border-radius:999px; background: var(--color-border); overflow:hidden;">
                                                <div style="width: {{ $review->{$key} }}%; height:100%; background: var(--color-primary);"></div>
                                            </div>
                                            <span style="font-weight: var(--weight-semibold); width:3ch; text-align:right;">{{ $review->{$key} ?? '—' }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>

            @if ($review->strengths || $review->improvements || $review->recommendations)
                <x-card>
                    <div class="flex flex-col" style="gap: var(--space-3);">
                        @if ($review->strengths)
                            <div class="text-sm"><span class="text-muted" style="font-weight: var(--weight-semibold);">{{ __('Strengths') }}:</span><br />{{ $review->strengths }}</div>
                        @endif
                        @if ($review->improvements)
                            <div class="text-sm"><span class="text-muted" style="font-weight: var(--weight-semibold);">{{ __('Areas for improvement') }}:</span><br />{{ $review->improvements }}</div>
                        @endif
                        @if ($review->recommendations)
                            <div class="text-sm"><span class="text-muted" style="font-weight: var(--weight-semibold);">{{ __('Recommendations') }}:</span><br />{{ $review->recommendations }}</div>
                        @endif
                    </div>
                </x-card>
            @endif
        </div>

        <x-card :title="__('Summary')">
            <div class="flex flex-col" style="gap: var(--space-2);">
                <div class="flex" style="justify-content:space-between; align-items:center;">
                    <span class="text-sm text-muted">{{ __('Overall score') }}</span>
                    <span style="font-size: var(--font-lg); font-weight: var(--weight-semibold);">{{ $review->overall_score }} / 100</span>
                </div>
                <x-badge :color="match (true) { $review->overall_score >= 85 => 'success', $review->overall_score >= 70 => 'primary', $review->overall_score >= 50 => 'warning', default => 'danger' }">
                    {{ $review->ratingLabel() }}
                </x-badge>
                <hr style="border:none; border-top:1px solid var(--color-border);" />
                <div class="text-sm"><span class="text-muted">{{ __('Employee') }}:</span> {{ $review->employee->full_name }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Position') }}:</span> {{ $review->employee?->position?->name ?? '—' }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Department') }}:</span> {{ $review->employee?->department?->name ?? '—' }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Evaluator') }}:</span> {{ $review->evaluator?->first_name.' '.$review->evaluator?->last_name ?: '—' }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Status') }}:</span> <x-badge :color="$review->statusBadgeColor()">{{ $review->statusLabel() }}</x-badge></div>
            </div>
        </x-card>
    </div>

</x-layouts.app>
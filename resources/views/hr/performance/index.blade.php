<x-layouts.app :title="__('Performance Reviews')">

    <x-page-header :title="__('Performance Reviews')" :description="__('Evaluations across teaching, classroom and responsibility categories.')">
        @if (auth()->user()->hasPermission('hr.create'))
            <a href="{{ route('hr.performance.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                {{ __('New review') }}
            </a>
        @endif
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('hr.performance.index') }}" class="grid gap-1" style="grid-template-columns: 1fr 1fr auto auto; align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="period">{{ __('Period') }}</label>
                <select id="period" name="period" class="form-select">
                    <option value="">{{ __('All periods') }}</option>
                    @foreach ($periods as $p)
                        <option value="{{ $p }}" @selected(request('period') === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="status">{{ __('Status') }}</label>
                <select id="status" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (\App\Support\Enums\PerformanceReviewStatus::cases() as $case)
                        @if ($case->value !== 'archived')
                            <option value="{{ $case->value }}" @selected(request('status') === $case->value)>{{ $case->label() }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
            @if (request()->hasAny(['period', 'status']))
                <a href="{{ route('hr.performance.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
            @endif
        </form>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Period') }}</th>
                        <th>{{ __('Overall') }}</th>
                        <th>{{ __('Evaluator') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $review->employee->full_name }}</div>
                                <div class="text-xs text-muted">{{ $review->employee->employee_id }}</div>
                            </td>
                            <td class="text-sm">{{ $review->period }}</td>
                            <td>
                                <div style="display:flex; align-items:center; gap: var(--space-1);">
                                    <span style="font-weight: var(--weight-semibold);">{{ $review->overall_score }}</span>
                                    <span class="text-xs text-muted">/ 100</span>
                                    <x-badge :color="match (true) { $review->overall_score >= 85 => 'success', $review->overall_score >= 70 => 'primary', $review->overall_score >= 50 => 'warning', default => 'danger' }">{{ $review->ratingLabel() }}</x-badge>
                                </div>
                            </td>
                            <td class="text-sm text-muted">{{ $review->evaluator?->first_name.' '.$review->evaluator?->last_name ?: '—' }}</td>
                            <td><x-badge :color="$review->statusBadgeColor()">{{ $review->statusLabel() }}</x-badge></td>
                            <td class="actions-cell">
                                <a href="{{ route('hr.performance.show', $review) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('View') }}">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                                @if (auth()->user()->hasPermission('hr.edit'))
                                    <a href="{{ route('hr.performance.edit', $review) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('Edit') }}">
                                        <x-icon name="pencil" class="icon-sm" />
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="trending-up" :title="__('No performance reviews yet')" :message="__('Create a review to evaluate staff performance.')">
                                    <a href="{{ route('hr.performance.create') }}" class="btn btn-primary btn-sm">{{ __('New review') }}</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $reviews->links() }}</div>
    </x-card>

</x-layouts.app>
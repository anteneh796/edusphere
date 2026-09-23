<x-layouts.app :title="__('Report Cards')">
    <x-breadcrumb :items="[['label' => __('Report Cards')]]" />

    <x-page-header :title="__('Report Cards')" :description="__('Generate, review and publish student report cards.')">
        @can('create', \App\Domains\Exams\Models\Exam::class)
            <a href="{{ route('report-cards.create') }}" class="btn btn-primary">
                <x-icon name="file-text" class="icon-sm" />
                {{ __('New report card') }}
            </a>
        @endcan
    </x-page-header>

    <div class="grid grid-cards" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <x-stat-card :label="__('Total cards')" :value="$cards->total()" icon="file-text" color="primary" />
        <x-stat-card :label="__('Current page')" :value="$cards->count()" icon="clipboard-check" color="info" />
        <x-stat-card :label="__('Published')" :value="$cards->where('status.value', 'published')->count()" icon="check-circle" color="success" />
        <x-stat-card :label="__('Years')" :value="$years->count()" icon="calendar" color="neutral" />
    </div>

    <x-card class="mt-4">
        <form method="GET" action="{{ route('report-cards.index') }}" class="filter-grid">
            <div class="form-group">
                <label class="form-label" for="year-filter">{{ __('Academic year') }}</label>
                <select id="year-filter" name="year" class="form-select">
                    <option value="">{{ __('All years') }}</option>
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}" @selected(request('year') == $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="status-filter">{{ __('Status') }}</label>
                <select id="status-filter" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (\App\Support\Enums\ReportCardStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-1" style="align-items:end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="search" class="icon-sm" />
                    {{ __('Filter') }}
                </button>
                @if (request('year') || request('status'))
                    <a href="{{ route('report-cards.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Card #') }}</th>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('Exam') }}</th>
                        <th>{{ __('Year') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cards as $card)
                        <tr>
                            <td class="text-sm" style="font-weight: var(--weight-semibold);">
                                {{ $card->report_card_number ?? '—' }}
                            </td>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $card->student?->full_name ?? '—' }}</div>
                                <div class="text-xs text-muted">{{ $card->student?->student_number }}</div>
                            </td>
                            <td class="text-sm">{{ $card->exam?->name ?? '—' }}</td>
                            <td class="text-sm">{{ $card->academicYear?->name ?? '—' }}</td>
                            <td><x-badge :color="$card->status?->badgeColor()" :dot="true">{{ $card->status?->label() }}</x-badge></td>
                            <td class="actions-cell">
                                <a href="{{ route('report-cards.show', $card) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('View')">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="file-text" :title="__('No report cards yet')" :message="__('Generate a report card for an exam and a student to get started.')">
                                    @can('create', \App\Domains\Exams\Models\Exam::class)
                                        <a href="{{ route('report-cards.create') }}" class="btn btn-primary btn-sm">{{ __('New report card') }}</a>
                                    @endcan
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $cards->links() }}
        </div>
    </x-card>
</x-layouts.app>

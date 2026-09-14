<x-layouts.app :title="'Exams'">
    <x-breadcrumb :items="[['label' => 'Exams']]" />

    <x-page-header title="Exams & Results" description="Create examinations, assign papers and enter results.">
        @can('create', \App\Domains\Exams\Models\Exam::class)
            <a href="{{ route('exams.create') }}" class="btn btn-primary">
                <x-icon name="award" class="icon-sm" />
                New exam
            </a>
        @endcan
    </x-page-header>

    <div class="grid grid-cards" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <x-stat-card label="Draft exams" :value="$draftCount" icon="lock" color="neutral" />
        <x-stat-card label="Published" :value="$publishedCount" icon="layers" color="info" />
        <x-stat-card label="Result entries" :value="$resultCount" icon="clipboard-check" color="success" />
        <x-stat-card label="Current year" value="{{ $currentYear->name }}" icon="calendar" color="primary" />
    </div>

    <x-card class="mt-4">
        <form method="GET" action="{{ route('exams.index') }}" class="filter-grid">
            <div class="form-group">
                <label class="form-label" for="year-filter">Academic year</label>
                <select id="year-filter" name="year" class="form-select">
                    <option value="">All years</option>
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}" @selected(request('year') === $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="type-filter">Type</label>
                <select id="type-filter" name="type" class="form-select">
                    <option value="">All types</option>
                    @foreach (\App\Support\Enums\ExamType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="status-filter">Status</label>
                <select id="status-filter" name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach (\App\Support\Enums\ExamStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-1" style="align-items:end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="search" class="icon-sm" />
                    Filter
                </button>
                @if (request('year') || request('type') || request('status'))
                    <a href="{{ route('exams.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Exam</th>
                        <th>Year</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th>Papers</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exams as $exam)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $exam->name }}</div>
                                <div class="text-xs text-muted">Created by {{ $exam->createdBy?->name ?? '—' }}</div>
                            </td>
                            <td class="text-sm">{{ $exam->academicYear?->name ?? '—' }}</td>
                            <td><x-badge :color="$exam->type?->badgeColor()">{{ $exam->type?->label() }}</x-badge></td>
                            <td class="text-sm">
                                {{ $exam->start_date?->format('M j, Y') }}
                                @if ($exam->end_date)
                                    – {{ $exam->end_date->format('M j, Y') }}
                                @endif
                            </td>
                            <td><x-badge :color="$exam->status?->badgeColor()" :dot="true">{{ $exam->status?->label() }}</x-badge></td>
                            <td class="text-sm">{{ $exam->papers_count }} paper(s)</td>
                            <td class="actions-cell">
                                <a href="{{ route('exams.show', $exam) }}" class="btn btn-ghost btn-sm btn-icon" title="View">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="award" title="No exams found" message="Create an exam to schedule papers and enter results.">
                                    @can('create', \App\Domains\Exams\Models\Exam::class)
                                        <a href="{{ route('exams.create') }}" class="btn btn-primary btn-sm">New exam</a>
                                    @endcan
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $exams->links() }}
        </div>
    </x-card>
</x-layouts.app>
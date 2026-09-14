<x-layouts.app :title="$exam->name">
    <x-breadcrumb :items="[
        ['label' => 'Exams', 'url' => route('exams.index')],
        ['label' => $exam->name],
    ]" />

    <x-page-header :title="$exam->name"
        :description="$exam->type?->label().' · '.($exam->academicYear?->name ?? '—')">
        <div class="flex gap-1 flex-wrap-1" style="align-items:center;">
            @can('managePapers', $exam)
                <a href="{{ route('exams.papers.edit', $exam) }}" class="btn btn-primary">
                    <x-icon name="list" class="icon-sm" />
                    Papers
                </a>
            @endcan

            @can('update', $exam)
                <a href="{{ route('exams.edit', $exam) }}" class="btn btn-secondary">
                    <x-icon name="pencil" class="icon-sm" />
                    Edit
                </a>

                @if ($exam->isPublished() && ! $exam->isCompleted())
                    <form method="POST" action="{{ route('exams.complete', $exam) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            <x-icon name="check-circle" class="icon-sm" />
                            Complete
                        </button>
                    </form>
                @elseif (! $exam->isPublished())
                    <form method="POST" action="{{ route('exams.publish', $exam) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            <x-icon name="layers" class="icon-sm" />
                            Publish
                        </button>
                    </form>
                @endif

                @can('delete', $exam)
                    <form method="POST" action="{{ route('exams.destroy', $exam) }}"
                        onsubmit="return confirm('Delete this exam and all of its results? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-icon text-danger" title="Delete">
                            <x-icon name="trash" class="icon-sm" />
                        </button>
                    </form>
                @endcan
            @endcan
        </div>
    </x-page-header>

    <div class="chips">
        <span class="chip"><x-icon name="layers" class="icon-sm" /><span>{{ $summary['papers'] }} paper(s)</span></span>
        <span class="chip"><x-icon name="clipboard-check" class="icon-sm" /><span>{{ $summary['entered'] }} result(s) entered</span></span>
        <span class="chip"><x-icon name="calendar" class="icon-sm" />
            <span>{{ $exam->start_date?->format('M j, Y') }}@if ($exam->end_date) – {{ $exam->end_date->format('M j, Y') }}@endif</span>
        </span>
        <span class="chip"><x-badge :color="$exam->status?->badgeColor()" :dot="true">{{ $exam->status?->label() }}</x-badge></span>
    </div>

    @if ($exam->description)
        <x-card class="mt-4" :title="'About this exam'">
            <p class="text-sm" style="white-space: pre-line;">{{ $exam->description }}</p>
        </x-card>
    @endif

    <div style="margin-top: var(--space-4); display:flex; flex-direction:column; gap: var(--space-4);">
        @forelse ($papersByClass as $group)
            <x-card :title="$group['classRoom']->name" :subtitle="$group['classRoom']->gradeLevel?->name ?? ''">
                @if ($group['papers']->isEmpty())
                    <x-empty-state icon="book" title="No papers assigned" message="Add papers for this class." />
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Max / Pass</th>
                                    <th>Exam date</th>
                                    <th>Results</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($group['papers'] as $paper)
                                    <tr>
                                        <td>
                                            <div style="font-weight: var(--weight-semibold);">{{ $paper->subject?->name }}</div>
                                            @if ($paper->instruction)
                                                <div class="text-xs text-muted">{{ $paper->instruction }}</div>
                                            @endif
                                        </td>
                                        <td class="text-sm">{{ $paper->max_marks }} / {{ $paper->pass_marks }}</td>
                                        <td class="text-sm">{{ $paper->exam_date?->format('M j, Y') ?? '—' }}</td>
                                        <td class="text-sm">
                                            @if ($paper->results->isNotEmpty())
                                                {{ $paper->results->count() }} entered
                                            @else
                                                <span class="text-muted">Not entered</span>
                                            @endif
                                        </td>
                                        <td class="actions-cell">
                                            @can('enterResults', $exam)
                                                <a href="{{ route('exams.results', $paper) }}" class="btn btn-primary btn-sm btn-icon" title="Enter results">
                                                    <x-icon name="clipboard-check" class="icon-sm" />
                                                </a>
                                            @endcan
                                            @if ($paper->results->isNotEmpty())
                                                <a href="{{ route('exams.results', $paper) }}" class="btn btn-ghost btn-sm btn-icon" title="Review results">
                                                    <x-icon name="eye" class="icon-sm" />
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        @empty
            <x-card>
                <x-empty-state icon="award" title="No papers yet" message="Assign subjects and classes to schedule this exam.">
                    @can('managePapers', $exam)
                        <a href="{{ route('exams.papers.edit', $exam) }}" class="btn btn-primary btn-sm">Assign papers</a>
                    @endcan
                </x-empty-state>
            </x-card>
        @endforelse
    </div>
</x-layouts.app>
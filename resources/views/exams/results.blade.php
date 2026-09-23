<x-layouts.app :title="'Results · '.$paper->subject->name.' · '.$paper->classRoom->name">
    @php
        $maxLabel = rtrim(rtrim(number_format($paper->max_marks, 2), '0'), '.');
    @endphp

    <x-breadcrumb :items="[
        ['label' => 'Exams', 'url' => route('exams.index')],
        ['label' => $paper->exam->name, 'url' => route('exams.show', $paper->exam)],
        ['label' => $paper->subject->name.' · '.$paper->classRoom->name],
    ]" />

    <x-page-header :title="$paper->subject->name"
        :description="'Enter results for '.$paper->classRoom->name.' · '.($paper->classRoom->gradeLevel?->name ?? '').' · max '.$maxLabel.' marks'">
        <span style="display:inline-flex; align-items:center; gap: var(--space-2); flex-wrap:wrap;">
            <x-badge :color="$paper->exam->status?->badgeColor()" :dot="true">{{ $paper->exam->status?->label() }}</x-badge>
            <a href="{{ route('exams.show', $paper->exam) }}" class="btn btn-secondary">
                <x-icon name="chevron-left" class="icon-sm" />
                Back to exam
            </a>
        </span>
    </x-page-header>

    <div class="grid" style="grid-template-columns: 1fr 300px; gap: var(--space-4); align-items:start;"
        x-data="resultsBoard(@js($rows->map(fn ($row) => [
            'student_id' => $row['student']->getKey(),
            'name' => $row['student']->full_name,
            'number' => $row['student']->student_number,
            'marks' => $row['result']?->marks_obtained ?? '',
            'remarks' => $row['result']?->remarks ?? '',
        ])->values()), {{ $paper->id }})">

        <x-card>
            <form method="POST" action="{{ route('exams.results.save', $paper) }}" @submit.prevent="handleSubmit($event)">
                @csrf
                @method('PUT')

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th style="width: 120px;">Marks / {{ $maxLabel }}</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $i => $row)
                                <tr>
                                    <td class="text-sm text-muted">{{ $loop->iteration }}</td>
                                    <td class="text-sm">
                                        <div style="font-weight: var(--weight-semibold);">{{ $row['student']->full_name }}</div>
                                        <div class="text-xs text-muted">{{ $row['student']->student_number }}</div>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" :max="{{ $paper->max_marks }}"
                                            class="form-input" style="width: 120px;"
                                            :name="`results[${index($i)}][marks_obtained]`"
                                            x-model="rows[{{ $i }}].marks"
                                            placeholder="—" />
                                    </td>
                                    <td>
                                        <input type="text" class="form-input" maxlength="255" placeholder="Optional…"
                                            :name="`results[${index($i)}][remarks]`"
                                            x-model="rows[{{ $i }}].remarks" />
                                    </td>
                                    <input type="hidden" :name="`results[${index($i)}][student_id]`" :value="rows[{{ $i }}].student_id" />
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <x-empty-state icon="users" title="No students in this class" message="Enroll students into this class first." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer" style="display:flex; justify-content: space-between; align-items:center; flex-wrap:wrap; gap: var(--space-2);">
                    <div class="text-sm text-muted">
                        <b x-text="filled"></b> of {{ $rows->count() }} students scored.
                    </div>
                    <div class="flex gap-1">
                        <a href="{{ route('exams.show', $paper->exam) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            <x-icon name="check" class="icon-sm" />
                            Save results
                        </button>
                    </div>
                </div>
            </form>
        </x-card>

        <div style="display:flex; flex-direction:column; gap: var(--space-4);">
            <x-card>
                <h3 class="h4" style="margin-bottom: var(--space-2);">Summary</h3>
                <div class="chips" style="flex-wrap:wrap;">
                    <span class="chip"><b>{{ $summary['total'] }}</b>&nbsp;entered</span>
                    <span class="chip"><b>{{ $summary['average'] }}</b>&nbsp;avg</span>
                    <span class="chip chip-success"><b>{{ $summary['passing'] }}</b>&nbsp;passing</span>
                    <span class="chip chip-danger"><b>{{ $summary['failing'] }}</b>&nbsp;failing</span>
                    <span class="chip"><b>{{ $summary['best'] }}</b>&nbsp;best</span>
                </div>
            </x-card>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('resultsBoard', (seedRows, paperId) => ({
                    rows: seedRows,
                    saving: false,

                    index(i) {
                        return i;
                    },

                    get filled() {
                        return this.rows.filter(r => r.marks !== '' && r.marks !== null).length;
                    },

                    handleSubmit(event) {
                        event.target.submit();
                    },
                }));
            });
        </script>
    @endpush
</x-layouts.app>
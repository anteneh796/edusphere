<x-layouts.app :title="'Papers · '.$exam->name">
    <x-breadcrumb :items="[
        ['label' => 'Exams', 'url' => route('exams.index')],
        ['label' => $exam->name, 'url' => route('exams.show', $exam)],
        ['label' => 'Papers'],
    ]" />

    <x-page-header :title="'Papers · '.$exam->name"
        :description="'Assign the subjects, maximum marks and dates for each class sitting '.$exam->name.'.'" />

    <div style="max-width: 1080px;">
        <x-card>
            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom: var(--space-3);">
                    <x-icon name="alert-triangle" class="icon-sm" />
                    Please review the paper assignments below.
                    <ul style="margin-top:4px; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('exams.papers.update', $exam) }}" novalidate
                x-data="papersForm(@js($existing))">
                @csrf
                @method('PUT')

                <div class="table-responsive">
                    <table class="table" style="min-width: 860px;">
                        <thead>
                            <tr>
                                <th style="width: 22%;">Class</th>
                                <th style="width: 24%;">Subject</th>
                                <th style="width: 10%;">Max / Pass</th>
                                <th style="width: 10%;">Date</th>
                                <th style="width: 24%;">Instruction</th>
                                <th class="text-right" style="width: 6%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="index">
                                <tr>
                                    <td>
                                        <select class="form-select" :name="`papers[${index}][class_room_id]`" x-model="row.class_room_id" :required="index === 0">
                                            <option value="">Select class…</option>
                                            @foreach ($classes as $class)
                                                <option value="{{ $class->id }}" x-text="'{{ $class->name }} · {{ $class->gradeLevel?->name }}'"></option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select" :name="`papers[${index}][subject_id]`" x-model="row.subject_id" :required="index === 0">
                                            <option value="">Select subject…</option>
                                            @foreach ($subjects as $subject)
                                                <option value="{{ $subject->id }}" x-text="'{{ $subject->code }} — {{ $subject->name }}'"></option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="flex gap-1">
                                            <input type="number" step="0.01" min="1" class="form-control" style="width: 72px;" :name="`papers[${index}][max_marks]`" x-model="row.max_marks" placeholder="100" />
                                            <input type="number" step="0.01" min="0" class="form-control" style="width: 72px;" :name="`papers[${index}][pass_marks]`" x-model="row.pass_marks" placeholder="50" />
                                        </div>
                                    </td>
                                    <td>
                                        <input type="date" class="form-control" :name="`papers[${index}][exam_date]`" x-model="row.exam_date" />
                                    </td>
                                    <td>
                                        <input type="text" maxlength="500" class="form-control" :name="`papers[${index}][instruction]`" x-model="row.instruction" placeholder="Optional notes…" />
                                    </td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" @click="rows.splice(index, 1)" title="Remove paper">
                                            <x-icon name="x" class="icon-sm" />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex align-center" style="gap: var(--space-2); margin-top: var(--space-3); flex-wrap: wrap;">
                    <button type="button" class="btn btn-secondary" @click="addRow()">
                        <x-icon name="plus" class="icon-sm" />
                        Add paper
                    </button>
                    <div class="flex-1" style="flex:1;"></div>
                    <a href="{{ route('exams.show', $exam) }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Save papers
                    </button>
                </div>
            </form>
        </x-card>
    </div>

    <script>
        function papersForm(existing) {
            return {
                rows: (existing ?? []).map((row) => ({
                    class_room_id: row.class_room_id ?? '',
                    subject_id: row.subject_id ?? '',
                    max_marks: row.max_marks ?? '',
                    pass_marks: row.pass_marks ?? '',
                    weight: row.weight ?? '',
                    exam_date: row.exam_date ?? '',
                    instruction: row.instruction ?? '',
                })),
                addRow() {
                    this.rows.push({
                        class_room_id: '', subject_id: '', max_marks: '',
                        pass_marks: '', weight: '', exam_date: '', instruction: '',
                    });
                },
            };
        }
        window.papersForm = papersForm;
    </script>
</x-layouts.app>
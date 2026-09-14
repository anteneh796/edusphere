<x-layouts.app :title="'Subjects · '.$classRoom->name">
    <x-breadcrumb :items="[
        ['label' => 'Academics', 'url' => route('academics.index')],
        ['label' => 'Classes', 'url' => route('academics.classes.index')],
        ['label' => $classRoom->name, 'url' => route('academics.classes.subjects', $classRoom)],
        ['label' => 'Subjects & Teachers'],
    ]" />

    <x-page-header :title="'Subjects & Teachers · '.$classRoom->name"
        :description="'Assign curriculum subjects, weekly periods and a teacher to each section of '.$classRoom->gradeLevel->name.'.'" />

    <div style="max-width: 880px;">
        <x-card>
            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom: var(--space-3);">
                    <x-icon name="alert-triangle" class="icon-sm" />
                    Please review the subject assignments below.
                    <ul style="margin-top:4px; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('academics.classes.subjects.update', $classRoom) }}" novalidate
                x-data="subjectForm(@js($existing))">
                @csrf
                @method('PUT')

                <div class="table-responsive">
                    <table class="table" style="min-width: 560px;">
                        <thead>
                            <tr>
                                <th style="width: 38%;">Subject</th>
                                <th style="width: 34%;">Teacher</th>
                                <th style="width: 18%;">Periods / wk</th>
                                <th class="text-right" style="width: 10%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="index">
                                <tr>
                                    <td>
                                        <select class="form-select" :name="`subjects[${index}][subject_id]`" x-model="row.subject_id" :required="index === 0">
                                            <option value="">Select subject…</option>
                                            @foreach ($subjects as $subject)
                                                <option value="{{ $subject->id }}" x-text="'{{ $subject->code }} — {{ $subject->name }}'"></option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select" :name="`subjects[${index}][teacher_id]`" x-model="row.teacher_id">
                                            <option value="">Unassigned</option>
                                            @foreach ($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" x-text="'{{ $teacher->full_name }}'"></option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="40" class="form-control" :name="`subjects[${index}][periods_per_week]`" x-model="row.periods_per_week" placeholder="—" />
                                    </td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" @click="rows.splice(index, 1)" title="Remove subject">
                                            <x-icon name="x" class="icon-sm" />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex align-center" style="gap: var(--space-2); margin-top: var(--space-3);">
                    <button type="button" class="btn btn-secondary" @click="addRow()">
                        <x-icon name="plus" class="icon-sm" />
                        Add subject
                    </button>
                    <div class="flex-1" style="flex:1;"></div>
                    <a href="{{ route('academics.classes.index') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Save assignments
                    </button>
                </div>
            </form>
        </x-card>
    </div>

    <script>
        function subjectForm(existing) {
            return {
                rows: (existing ?? []).map((row) => ({
                    subject_id: row.subject_id ?? '',
                    teacher_id: row.teacher_id ?? '',
                    periods_per_week: row.periods_per_week ?? '',
                })),
                addRow() {
                    this.rows.push({ subject_id: '', teacher_id: '', periods_per_week: '' });
                },
            };
        }
        window.subjectForm = subjectForm;
    </script>
</x-layouts.app>
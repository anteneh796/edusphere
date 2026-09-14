<x-layouts.app :title="'Classes'">
    <x-breadcrumb :items="[
        ['label' => 'Academics', 'url' => route('academics.index')],
        ['label' => 'Classes'],
    ]" />

    <x-page-header title="Classes" description="Sections for {{ $currentYear->name }} and their teacher assignments.">
        @can('create', \App\Domains\Academics\Models\ClassRoom::class)
            <a href="{{ route('academics.classes.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                New class
            </a>
        @endcan
    </x-page-header>

    @forelse ($grades as $row)
        <x-card class="mb-4">
            <div class="flex align-center" style="justify-content: space-between; margin-bottom: var(--space-3);">
                <div>
                    <h3 class="h4" style="margin-bottom:2px;">{{ $row['grade']->name }}</h3>
                    <p class="text-sm text-muted">{{ $row['classes']->count() }} section(s) · {{ $row['student_count'] }} student(s)</p>
                </div>
                <span class="code-chip">{{ $row['grade']->code }}</span>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Capacity</th>
                            <th>Students</th>
                            <th>Subjects</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($row['classes'] as $class)
                            <tr>
                                <td style="font-weight: var(--weight-semibold);">{{ $class->name }}</td>
                                <td class="text-sm">{{ $class->capacity ?? '—' }}</td>
                                <td class="text-sm">
                                    <x-badge :color="$class->active_students >= ($class->capacity ?? 0) ? 'danger' : 'primary'">{{ $class->active_students }}</x-badge>
                                </td>
                                <td class="text-sm">
                                    <a href="{{ route('academics.classes.subjects', $class) }}" class="text-sm">{{ $class->subject_count }} subject(s)</a>
                                </td>
                                <td class="actions-cell">
                                    <a href="{{ route('academics.classes.subjects', $class) }}" class="btn btn-ghost btn-sm btn-icon" title="Subjects & teachers">
                                        <x-icon name="book-open" class="icon-sm" />
                                    </a>
                                    @can('update', $class)
                                        <a href="{{ route('academics.classes.edit', $class) }}" class="btn btn-ghost btn-sm btn-icon" title="Edit">
                                            <x-icon name="pencil" class="icon-sm" />
                                        </a>
                                    @endcan
                                    @can('delete', $class)
                                        <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" title="Delete"
                                            @click="$store.confirm.ask({
                                                title: 'Delete class?',
                                                message: `Delete ${@js($class->name)} and its subject assignments.`,
                                                action: @js(route('academics.classes.destroy', $class)),
                                                method: 'DELETE',
                                                confirmText: 'Delete'
                                            })">
                                            <x-icon name="trash" class="icon-sm" />
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state icon="book-open" title="No sections for this grade" message="Create a class section for this grade.">
                                        @can('create', \App\Domains\Academics\Models\ClassRoom::class)
                                            <a href="{{ route('academics.classes.create') }}" class="btn btn-primary btn-sm">New class</a>
                                        @endcan
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    @empty
        <x-card>
            <x-empty-state icon="book-open" title="No classes configured" message="Add class sections for the current academic year." />
        </x-card>
    @endforelse
</x-layouts.app>
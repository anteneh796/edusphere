<x-layouts.app :title="'Subjects'">
    <x-breadcrumb :items="[
        ['label' => 'Academics', 'url' => route('academics.index')],
        ['label' => 'Subjects'],
    ]" />

    <x-page-header title="Subjects" description="Curriculum and the number of class sections each is taught in.">
        @can('create', \App\Domains\Academics\Models\Subject::class)
            <a href="{{ route('academics.subjects.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                New subject
            </a>
        @endcan
    </x-page-header>

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th class="text-right">Sections</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjects as $subject)
                        <tr>
                            <td style="font-weight: var(--weight-semibold);">{{ $subject->name }}</td>
                            <td><span class="code-chip">{{ $subject->code }}</span></td>
                            <td class="text-sm text-muted">{{ $subject->description ?? '—' }}</td>
                            <td class="text-right text-sm"><x-badge color="info">{{ $subject->section_count }}</x-badge></td>
                            <td class="actions-cell">
                                @can('update', $subject)
                                    <a href="{{ route('academics.subjects.edit', $subject) }}" class="btn btn-ghost btn-sm btn-icon" title="Edit">
                                        <x-icon name="pencil" class="icon-sm" />
                                    </a>
                                @endcan
                                @can('delete', $subject)
                                    <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" title="Delete"
                                        @click="$store.confirm.ask({
                                            title: 'Delete subject?',
                                            message: `Delete ${@js($subject->name)} (${@js($subject->code)}).`,
                                            action: @js(route('academics.subjects.destroy', $subject)),
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
                                <x-empty-state icon="book-open" title="No subjects" message="Create the first curriculum subject.">
                                    @can('create', \App\Domains\Academics\Models\Subject::class)
                                        <a href="{{ route('academics.subjects.create') }}" class="btn btn-primary btn-sm">New subject</a>
                                    @endcan
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>
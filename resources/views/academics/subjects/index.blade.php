<x-layouts.app :title="__('Subjects')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Subjects')],
    ]" />

    <x-page-header :title="__('Subjects')" :description="__('Curriculum and the number of class sections each is taught in.')">
        @can('create', \App\Domains\Academics\Models\Subject::class)
            <a href="{{ route('academics.subjects.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                {{ __('New subject') }}
            </a>
        @endcan
    </x-page-header>

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Subject') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th class="text-right">{{ __('Sections') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
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
                                    <a href="{{ route('academics.subjects.edit', $subject) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('Edit')">
                                        <x-icon name="pencil" class="icon-sm" />
                                    </a>
                                @endcan
                                @can('delete', $subject)
                                    <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" :title="__('Delete')"
                                        @click="$store.confirm.ask({
                                            title: @js(__('Delete subject?')),
                                            message: @js(__('Delete').' '.$subject->name.' ('.$subject->code.').'),
                                            action: @js(route('academics.subjects.destroy', $subject)),
                                            method: 'DELETE',
                                            confirmText: @js(__('Delete'))
                                        })">
                                        <x-icon name="trash" class="icon-sm" />
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="book-open" :title="__('No subjects')" :message="__('Create the first curriculum subject.')">
                                    @can('create', \App\Domains\Academics\Models\Subject::class)
                                        <a href="{{ route('academics.subjects.create') }}" class="btn btn-primary btn-sm">{{ __('New subject') }}</a>
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
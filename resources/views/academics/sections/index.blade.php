<x-layouts.app :title="__('Sections')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Sections')],
    ]" />

    <x-page-header :title="__('Sections')" :description="__('Section letters (A, B, C…) available for classes in :year.', ['year' => $year->name])">
        @can('create', App\Domains\Academics\Models\Section::class)
            <a href="{{ route('academics.sections.create') }}" class="btn btn-primary btn-sm">
                <x-icon name="plus" class="icon-sm" />
                {{ __('New section') }}
            </a>
        @endcan
    </x-page-header>

    <x-card>
        <div class="flex gap-2 flex-wrap" style="margin-bottom: var(--space-3);">
            @foreach ($years as $aYear)
                <a href="{{ route('academics.sections.index', ['year' => $aYear]) }}"
                    class="badge {{ $aYear->getKey() === $year->getKey() ? 'badge-primary' : 'badge-neutral' }}"
                    style="text-decoration:none;">
                    {{ $aYear->name }}
                </a>
            @endforeach
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Section') }}</th>
                        <th>{{ __('Order') }}</th>
                        <th>{{ __('Used by classes') }}</th>
                        @if (auth()->user()->hasAnyPermission(['academics.edit', 'academics.delete']))
                            <th class="text-right">{{ __('Actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sections as $section)
                        <tr>
                            <td>
                                <span class="code-chip">{{ $section->name }}</span>
                            </td>
                            <td class="text-sm text-muted">{{ $section->sort_order }}</td>
                            <td class="text-sm text-muted">{{ $year->name }}</td>
                            @if (auth()->user()->hasAnyPermission(['academics.edit', 'academics.delete']))
                                <td class="text-right">
                                    <div class="flex gap-1 justify-end">
                                        @can('update', $section)
                                            <a href="{{ route('academics.sections.edit', $section) }}" class="btn btn-icon btn-secondary btn-sm" title="{{ __('Edit') }}">
                                                <x-icon name="pencil" class="icon-sm" />
                                            </a>
                                        @endcan
                                        @can('delete', $section)
                                            <form method="POST" action="{{ route('academics.sections.destroy', $section) }}" onsubmit="return confirm('{{ __('Delete this section?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon btn-secondary btn-sm" title="{{ __('Delete') }}">
                                                    <x-icon name="trash" class="icon-sm" />
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state icon="layers" :title="__('No sections')" :message="__('Add section letters that classes can reuse within this academic year.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>
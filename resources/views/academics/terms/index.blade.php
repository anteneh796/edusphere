<x-layouts.app :title="__('Terms')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Terms')],
    ]" />

    <x-page-header :title="__('Terms')" :description="__('Terms or semesters within the current academic year.')">
        @can('create', App\Domains\Academics\Models\AcademicTerm::class)
            <a href="{{ route('academics.terms.create') }}" class="btn btn-primary btn-sm">
                <x-icon name="plus" class="icon-sm" />
                {{ __('New term') }}
            </a>
        @endcan
    </x-page-header>

    <x-card :title="__('Terms · :year', ['year' => $year->name])">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Term') }}</th>
                        <th>{{ __('Sequence') }}</th>
                        <th>{{ __('Period') }}</th>
                        <th>{{ __('Status') }}</th>
                        @if (auth()->user()->hasAnyPermission(['academics.edit', 'academics.delete']))
                            <th class="text-right">{{ __('Actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($terms as $term)
                        <tr>
                            <td>
                                <span style="font-weight: var(--weight-semibold);">{{ $term->name }}</span>
                            </td>
                            <td class="text-sm text-muted">{{ $term->sequence }}</td>
                            <td class="text-sm text-muted">
                                @if ($term->start_date && $term->end_date)
                                    {{ $term->start_date->format('M j') }} – {{ $term->end_date->format('M j, Y') }}
                                @else
                                    {{ __('Not set') }}
                                @endif
                            </td>
                            <td>
                                @if ($term->is_current)
                                    <x-badge :label="__('Current')" color="success" dot />
                                @else
                                    <x-badge :label="__('Upcoming')" color="neutral" />
                                @endif
                            </td>
                            @if (auth()->user()->hasAnyPermission(['academics.edit', 'academics.delete']))
                                <td class="text-right">
                                    <div class="flex gap-1 justify-end">
                                        @can('update', $term)
                                            @if (! $term->is_current)
                                                <form method="POST" action="{{ route('academics.terms.activate', $term) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary btn-sm">{{ __('Make current') }}</button>
                                                </form>
                                            @endif
                                            <a href="{{ route('academics.terms.edit', $term) }}" class="btn btn-icon btn-secondary btn-sm" title="{{ __('Edit') }}">
                                                <x-icon name="pencil" class="icon-sm" />
                                            </a>
                                        @endcan
                                        @can('delete', $term)
                                            <form method="POST" action="{{ route('academics.terms.destroy', $term) }}" onsubmit="return confirm('{{ __('Delete this term?') }}');">
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
                            <td colspan="5">
                                <x-empty-state icon="layers" :title="__('No terms')" :message="__('Add terms such as Term 1, Term 2 and Term 3 for the school calendar.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>
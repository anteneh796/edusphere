<x-layouts.app :title="__('Grades')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Grades')],
    ]" />

    <x-page-header :title="__('Grades')" :description="__('Kindergarten through Grade 8 level structure with classes and enrollments.')">
        @can('create', App\Domains\Academics\Models\GradeLevel::class)
            <a href="{{ route('academics.grades.create') }}" class="btn btn-primary btn-sm">
                <x-icon name="plus" class="icon-sm" />
                {{ __('Add grade') }}
            </a>
        @endcan
    </x-page-header>

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Grade') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Stage') }}</th>
                        <th>{{ __('Sections') }} ({{ $currentYear->name }})</th>
                        <th class="text-right">{{ __('Students') }}</th>
                        @if (auth()->user()->hasAnyPermission(['academics.edit', 'academics.delete']))
                            <th class="text-right">{{ __('Actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grades as $row)
                        <tr>
                            <td>
                                <span style="font-weight: var(--weight-semibold);">{{ $row['grade']->name }}</span>
                                <div class="text-xs text-muted">{{ $row['grade']->description ?? __('No description') }}</div>
                            </td>
                            <td><span class="code-chip">{{ $row['grade']->code }}</span></td>
                            <td>
                                @if ($row['grade']->stage)
                                    <x-badge :label="\App\Support\Enums\GradeStage::from($row['grade']->stage)->label()" color="info" />
                                @else
                                    <x-badge :label="__('Not offered')" color="danger" />
                                @endif
                            </td>
                            <td>
                                <div class="flex gap-1 flex-wrap">
                                    @forelse ($row['classes'] as $class)
                                        <a href="{{ route('academics.classes.subjects', $class) }}" class="badge badge-neutral" style="text-decoration:none;">{{ $class->name }}</a>
                                    @empty
                                        <span class="text-muted text-sm">{{ __('No sections') }}</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="text-right text-sm">
                                <strong>{{ $row['student_count'] }}</strong>
                            </td>
                            @if (auth()->user()->hasAnyPermission(['academics.edit', 'academics.delete']))
                                <td class="text-right">
                                    <div class="flex gap-1 justify-end">
                                        @can('update', $row['grade'])
                                            <a href="{{ route('academics.grades.edit', $row['grade']) }}" class="btn btn-icon btn-secondary btn-sm" title="{{ __('Edit') }}">
                                                <x-icon name="pencil" class="icon-sm" />
                                            </a>
                                        @endcan
                                        @can('delete', $row['grade'])
                                            <form method="POST" action="{{ route('academics.grades.destroy', $row['grade']) }}" onsubmit="return confirm('{{ __('Delete this grade level?') }}');">
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
                            <td colspan="7">
                                <x-empty-state icon="graduation" :title="__('No grades configured')" :message="__('Run the academic seeder or create a grade level.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>
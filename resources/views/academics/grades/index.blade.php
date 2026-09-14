<x-layouts.app :title="'Grades'">
    <x-breadcrumb :items="[
        ['label' => 'Academics', 'url' => route('academics.index')],
        ['label' => 'Grades'],
    ]" />

    <x-page-header title="Grades" description="KG-1 through Grade 12 levels with their classes and enrollments." />

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Code</th>
                        <th>Sections ({{ $currentYear->name }})</th>
                        <th class="text-right">Students</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grades as $row)
                        <tr>
                            <td>
                                <a href="{{ route('academics.classes.index') }}" style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">
                                    {{ $row['grade']->name }}
                                </a>
                                <div class="text-xs text-muted">{{ $row['grade']->description ?? 'No description' }}</div>
                            </td>
                            <td><span class="code-chip">{{ $row['grade']->code }}</span></td>
                            <td>
                                <div class="flex gap-1 flex-wrap">
                                    @forelse ($row['classes'] as $class)
                                        <a href="{{ route('academics.classes.subjects', $class) }}" class="badge badge-neutral" style="text-decoration:none;">{{ $class->name }}</a>
                                    @empty
                                        <span class="text-muted text-sm">No sections</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="text-right text-sm">
                                <strong>{{ $row['student_count'] }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state icon="graduation" title="No grades configured" message="Run the academic seeder or create a grade level." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>
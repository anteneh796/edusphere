<x-layouts.app :title="__('Promotions')">
    <x-breadcrumb :items="[
        ['label' => __('Students'), 'url' => route('students.index')],
        ['label' => __('Promotions')],
    ]" />

    <x-page-header :title="__('Promotions')" :description="__('Close the academic year by promoting eligible students to the next grade. Grade 8 students graduate.')" />

    <x-card>
        <form method="GET" action="{{ route('students.promote') }}" class="filter-grid">
            <div class="form-group">
                <label class="form-label" for="class-filter">{{ __('Source class') }} ({{ $year->name }})</label>
                <select id="class-filter" name="class_room_id" class="form-select">
                    <option value="">{{ __('Select class') }}…</option>
                    @foreach ($classRooms as $class)
                        <option value="{{ $class->id }}" @selected($selectedClass && $selectedClass->id === $class->id)>
                            {{ $class->gradeLevel->name }} · {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="target-year-filter">{{ __('Target academic year') }}</label>
                <select id="target-year-filter" name="target_academic_year_id" class="form-select">
                    @foreach ($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}" @selected((request('target_academic_year_id') ?? $nextYear?->id) === $academicYear->id)>
                            {{ $academicYear->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-1" style="align-items:end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="search" class="icon-sm" />
                    {{ __('Preview') }}
                </button>
                @if ($selectedClass)
                    <a href="{{ route('students.promote') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>
    </x-card>

    @if ($selectedClass)
        <form method="POST" action="{{ route('students.promote.store') }}" novalidate>
            @csrf
            <input type="hidden" name="class_room_id" value="{{ $selectedClass->id }}" />
            <input type="hidden" name="target_academic_year_id" value="{{ request('target_academic_year_id') ?? $nextYear?->id }}" />

            <x-card :title="__('Promote :grade', ['grade' => $selectedClass->gradeLevel->name.' · '.$selectedClass->name]) ">
                @if ($candidates->isEmpty())
                    <x-empty-state icon="trending-up" :title="__('No candidates')" message="This class has no active students to promote." />
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Student') }}</th>
                                    <th>{{ __('Roll') }}</th>
                                    <th>{{ __('Current') }}</th>
                                    <th>{{ __('Next placement') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($candidates as $candidate)
                                    @php
                                        $nextGrade = \App\Domains\Academics\Models\GradeLevel::ordered()
                                            ->where('sort_order', '>', $candidate->gradeLevel?->sort_order ?? 0)
                                            ->first();
                                        $section = strtoupper(trim((string) str($selectedClass->name)->afterLast(' ')));
                                        $placement = $nextGrade ? ($nextGrade->name.' · '.$section) : 'Graduated';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="avatar-cell">
                                                <x-avatar :initials="$candidate->initials()" size="sm" />
                                                <div style="min-width:0;">
                                                    <strong>{{ $candidate->full_name }}</strong>
                                                    <div class="text-xs text-muted">{{ $candidate->student_number }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-sm">{{ $candidate->activeEnrollment()?->roll_number ?? '—' }}</td>
                                        <td class="text-sm">{{ $candidate->gradeLevel?->name ?? '—' }} · {{ $candidate->classRoom?->name ?? '—' }}</td>
                                        <td>
                                            @if ($nextGrade)
                                                <x-badge color="info">{{ $placement }}</x-badge>
                                            @else
                                                <x-badge color="accent">{{ __('Graduated') }}</x-badge>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <x-input name="note" label="Promotion note (optional)" :value="old('note')" placeholder="e.g. Final results published. All students passed." />
                        <div class="flex gap-2 mt-2">
                            <button type="submit" class="btn btn-primary"
                                onclick="return confirm('Promote {{ $candidates->count() }} student(s) to the {{ request('target_academic_year_id') ? '' : ($nextYear?->name ?? 'next') }} academic year? Previous enrollments will be locked.')">
                                <x-icon name="trending-up" class="icon-sm" />
                                {{ __('Run promotion for :count students', ['count' => $candidates->count()]) }}
                            </button>
                            <a href="{{ route('students.promote') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        </div>
                    </div>
                @endif
            </x-card>
        </form>
    @else
        <x-card>
            <x-empty-state icon="trending-up" :title="__('Select a class to preview')" message="Choose a source class and target academic year to see which students will be promoted." />
        </x-card>
    @endif

</x-layouts.app>
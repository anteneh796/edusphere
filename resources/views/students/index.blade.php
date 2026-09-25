<x-layouts.app :title="__('Students')">
    <x-breadcrumb :items="[['label' => __('Students')]]" />

    <x-page-header :title="__('Students')" :description="__('Manage the complete KG through Grade 8 student record and enrollment lifecycle.')">
        <div class="flex gap-1 flex-wrap">
            <a href="{{ route('students.dashboard') }}" class="btn btn-secondary"><x-icon name="dashboard" class="icon-sm" /> {{ __('Dashboard') }}</a>
            @can('export', \App\Domains\Students\Models\Student::class)
                <a href="{{ route('students.export', request()->query()) }}" class="btn btn-secondary"><x-icon name="download" class="icon-sm" /> {{ __('Export CSV') }}</a>
            @endcan
            @can('create', \App\Domains\Students\Models\Student::class)
                <a href="{{ route('students.create') }}" class="btn btn-primary">
                <x-icon name="user-plus" class="icon-sm" />
                {{ __('Register student') }}
                </a>
            @endcan
        </div>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('students.index') }}" class="filter-grid">
            <x-input name="q" :label="__('Search')" :value="request('q')" :placeholder="__('Name or student number…')" wrapperClass="filter-q" />

            <div class="form-group">
                <label class="form-label" for="status-filter">{{ __('Status') }}</label>
                <select id="status-filter" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="grade-filter">{{ __('Grade') }}</label>
                <select id="grade-filter" name="grade_level_id" class="form-select">
                    <option value="">{{ __('All grades') }}</option>
                    @foreach ($gradeLevels as $grade)
                        <option value="{{ $grade->id }}" @selected(request('grade_level_id') === $grade->id)>{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="class-filter">{{ __('Class') }}</label>
                <select id="class-filter" name="class_room_id" class="form-select">
                    <option value="">{{ __('All classes') }}</option>
                    @foreach ($classRooms as $class)
                        <option value="{{ $class->id }}" @selected(request('class_room_id') === $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="gender-filter">{{ __('Gender') }}</label>
                <select id="gender-filter" name="gender" class="form-select">
                    <option value="">{{ __('All genders') }}</option>
                    <option value="male" @selected(request('gender') === 'male')>Male</option>
                    <option value="female" @selected(request('gender') === 'female')>Female</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="year-filter">{{ __('Academic year') }}</label>
                <select id="year-filter" name="academic_year_id" class="form-select">
                    <option value="">{{ __('All years') }}</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected(request('academic_year_id') === $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="transfer-filter">Transfer status</label>
                <select id="transfer-filter" name="transfer" class="form-select">
                    <option value="">{{ __('All students') }}</option>
                    <option value="1" @selected(request('transfer') === '1')>Transferred / Withdrawn</option>
                </select>
            </div>

            <div class="flex gap-1" style="align-items:end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="search" class="icon-sm" />
                    {{ __('Filter') }}
                </button>
                @if (request('q') || request('status') || request('grade_level_id') || request('class_room_id') || request('gender') || request('academic_year_id') || request('transfer'))
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('Student no.') }}</th>
                        <th>{{ __('Grade / Class') }}</th>
                        <th>{{ __('Guardian') }}</th>
                        <th>{{ __('Age') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td>
                                <div class="avatar-cell">
                                    <x-avatar :initials="$student->initials()" size="sm" />
                                    <div style="min-width:0;">
                                        <a href="{{ route('students.show', $student) }}" style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">
                                            {{ $student->full_name }}
                                        </a>
                                        <div class="text-xs text-muted">{{ \Illuminate\Support\Str::headline($student->gender) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="code-chip">{{ $student->student_number }}</span>
                            </td>
                            <td class="text-sm">
                                <div>{{ $student->gradeLevel->name ?? '—' }}</div>
                                <div class="text-xs text-muted">{{ $student->classRoom->name ?? '—' }}</div>
                            </td>
                            <td class="text-sm">
                                @if ($student->primaryGuardian)
                                    <div>{{ $student->primaryGuardian->full_name }}</div>
                                    <div class="text-xs text-muted">{{ $student->primaryGuardian->phone ?? '—' }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-sm">{{ $student->age() }} {{ __('yrs') }}</td>
                            <td>
                                <x-badge :color="$student->statusBadgeColor()">
                                    {{ $student->statusLabel() }}
                                </x-badge>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('students.show', $student) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('View')">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                                @can('update', $student)
                                    <a href="{{ route('students.edit', $student) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('Edit')">
                                        <x-icon name="pencil" class="icon-sm" />
                                    </a>
                                @endcan
                                @can('delete', $student)
                                    <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger"
                                        :title="__('Archive')"
                                        @click="$store.confirm.ask({
                                            title: @js(__('Archive student?')),
                                            message: @js(__('Archive').' '.$student->full_name.' ('.$student->student_number.'). '.__('This cannot be undone.')),
                                            action: @js(route('students.destroy', $student)),
                                            method: 'DELETE',
                                            confirmText: @js(__('Archive'))
                                        })">
                                        <x-icon name="archive" class="icon-sm" />
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="graduation" :title="__('No students found')" :message="__('Try adjusting your filters or register a new student.')">
                                    @can('create', \App\Domains\Students\Models\Student::class)
                                        <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm">{{ __('Register student') }}</a>
                                    @endcan
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $students->links() }}
        </div>
    </x-card>

</x-layouts.app>
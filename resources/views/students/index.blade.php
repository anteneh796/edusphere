<x-layouts.app :title="'Students'">
    <x-breadcrumb :items="[['label' => 'Students']]" />

    <x-page-header title="Students" description="Register and manage KG-12 student records.">
        @can('create', \App\Domains\Students\Models\Student::class)
            <a href="{{ route('students.create') }}" class="btn btn-primary">
                <x-icon name="user-plus" class="icon-sm" />
                Register student
            </a>
        @endcan
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('students.index') }}" class="filter-grid">
            <x-input name="q" label="Search" :value="request('q')" placeholder="Name or student number…" wrapperClass="filter-q" />

            <div class="form-group">
                <label class="form-label" for="status-filter">Status</label>
                <select id="status-filter" name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="grade-filter">Grade</label>
                <select id="grade-filter" name="grade_level_id" class="form-select">
                    <option value="">All grades</option>
                    @foreach ($gradeLevels as $grade)
                        <option value="{{ $grade->id }}" @selected(request('grade_level_id') === $grade->id)>{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="class-filter">Class</label>
                <select id="class-filter" name="class_room_id" class="form-select">
                    <option value="">All classes</option>
                    @foreach ($classRooms as $class)
                        <option value="{{ $class->id }}" @selected(request('class_room_id') === $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-1" style="align-items:end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="search" class="icon-sm" />
                    Filter
                </button>
                @if (request('q') || request('status') || request('grade_level_id') || request('class_room_id'))
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Student no.</th>
                        <th>Grade / Class</th>
                        <th>Guardian</th>
                        <th>Age</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
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
                            <td class="text-sm">{{ $student->age() }} yrs</td>
                            <td>
                                <x-badge :color="$student->statusBadgeColor()">
                                    {{ $student->statusLabel() }}
                                </x-badge>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('students.show', $student) }}" class="btn btn-ghost btn-sm btn-icon" title="View">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                                @can('update', $student)
                                    <a href="{{ route('students.edit', $student) }}" class="btn btn-ghost btn-sm btn-icon" title="Edit">
                                        <x-icon name="pencil" class="icon-sm" />
                                    </a>
                                @endcan
                                @can('delete', $student)
                                    <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger"
                                        title="Archive"
                                        @click="$store.confirm.ask({
                                            title: 'Archive student?',
                                            message: `Archive ${@js($student->full_name)} (${@js($student->student_number)}). This cannot be undone.`,
                                            action: @js(route('students.destroy', $student)),
                                            method: 'DELETE',
                                            confirmText: 'Archive'
                                        })">
                                        <x-icon name="archive" class="icon-sm" />
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="graduation" title="No students found" message="Try adjusting your filters or register a new student.">
                                    @can('create', \App\Domains\Students\Models\Student::class)
                                        <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm">Register student</a>
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
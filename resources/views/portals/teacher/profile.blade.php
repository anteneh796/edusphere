<x-layouts.app :title="__('My profile')">
    <x-page-header :title="__('My profile')" :description="__('Your account and teaching responsibilities.')" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3" style="align-items:start;">
        <div style="display:flex; flex-direction:column; gap: var(--space-4);">
            <x-card :title="__('Account')">
                <div class="flex items-center gap-4" style="margin-bottom: var(--space-3);">
                    <x-avatar :initials="$user->initials()" />
                    <div>
                        <p class="font-medium">{{ $user->full_name }}</p>
                        <p class="text-sm text-foreground-muted">{{ $user->email }}</p>
                    </div>
                </div>
                <dl class="detail-list">
                    <div>
                        <dt>{{ __('Staff type') }}</dt>
                        <dd>{{ $user->staff_type ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Department') }}</dt>
                        <dd>{{ $user->department ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Job title') }}</dt>
                        <dd>{{ $user->job_title ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Employee ID') }}</dt>
                        <dd>{{ $user->employee_id ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Roles') }}</dt>
                        <dd>{{ $user->roles->pluck('name')->map('ucfirst')->join(', ') ?: '—' }}</dd>
                    </div>
                </dl>
            </x-card>
        </div>

        <div style="display:flex; flex-direction:column; gap: var(--space-4);">
            <div class="grid" style="grid-template-columns: repeat(3, 1fr); gap: var(--space-3);">
                <x-stat-card :label="__('Lesson plans')" :value="$lessonPlanCount" icon="calendar-edit" color="primary" />
                <x-stat-card :label="__('Homework')" :value="$homeworkCount" icon="clipboard-list" color="success" />
                <x-stat-card :label="__('Assessments')" :value="$assessmentCount" icon="award" color="warning" />
            </div>

            <x-card :title="__('My classes')">
                @forelse ($classSubjects as $classSubject)
                    <div class="flex items-center justify-between gap-4 py-3 border-b border-border last:border-0">
                        <div>
                            <p class="font-medium">{{ $classSubject->subject?->name ?? '—' }}</p>
                            <p class="text-sm text-foreground-muted">{{ $classSubject->classRoom?->gradeLevel?->name ?? '' }} · {{ $classSubject->classRoom?->name ?? '' }}</p>
                        </div>
                        @if ($classSubject->is_homeroom)
                            <span class="badge badge-warning">{{ __('Homeroom') }}</span>
                        @endif
                    </div>
                @empty
                    <x-empty-state icon="book-open" :title="__('No classes assigned')" :message="__('Your assigned classes will appear here.')" />
                @endforelse
            </x-card>
        </div>
    </div>
</x-layouts.app>
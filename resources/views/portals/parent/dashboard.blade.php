<x-layouts.app :title="__('Parent dashboard')">
    <x-page-header
        :title="__('Welcome back, :name', ['name' => $guardian?->full_name ?? __('parent')])"
        :description="__('Everything about your children\u2019s school life in one place.')">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    @if (! $guardian)
        <x-card :title="__('Account not linked')">
            <x-empty-state
                icon="users"
                :title="__('No guardian profile linked')"
                :message="__('Ask the registrar to link a guardian profile to your login so you can view your children.')" />
        </x-card>
    @elseif (! $ward)
        <x-card :title="__('No children linked yet')">
            <x-empty-state
                icon="users"
                :title="__('No wards on this account')"
                :message="__('Once enrolled, your children will appear here so you can track attendance, grades and school updates.')" />
        </x-card>
    @else
        <div class="grid grid-stats">
            <x-stat-card
                :label="__('Attendance rate')"
                :value="isset($attendance['percentage']) ? number_format($attendance['percentage'], 1).'%' : '—'"
                icon="clipboard-check"
                color="success" />
            <x-stat-card
                :label="__('Absences')"
                :value="number_format($attendance['absent'] ?? 0)"
                icon="alert-triangle"
                color="danger" />
            <x-stat-card
                :label="__('Homework due')"
                :value="$homework->count()"
                icon="pencil"
                color="info" />

        </div>

        <div class="grid grid-2 mt-4">
            <x-card :title="__('Today')" subtitle="{{ $ward->full_name }}">
                @if ($todayStatus)
                    <div class="list-row">
                        <x-icon name="clipboard-check" class="icon-sm" />
                        <span>{{ __('Attendance today:') }}</span>
                        @php
                            $statusMap = ['present' => ['success', __('Present')], 'late' => ['warning', __('Late')], 'absent' => ['danger', __('Absent')], 'excused' => ['info', __('Excused')]];
                        @endphp
                        <span class="badge badge-{{ $statusMap[$todayStatus][0] ?? 'neutral' }}">{{ $statusMap[$todayStatus][1] ?? ucfirst($todayStatus) }}</span>
                    </div>
                @else
                    <div class="list-row">
                        <x-icon name="clock" class="icon-sm" />
                        <span>{{ __('No attendance marked yet today.') }}</span>
                    </div>
                @endif
                <a href="{{ route('cms.parent.attendance') }}" class="btn btn-ghost btn-sm mt-2">{{ __('View attendance') }}</a>
            </x-card>

            <x-card :title="__('Upcoming events')">
                @forelse ($upcomingEvents as $event)
                    <div class="list-row">
                        <x-icon name="calendar" class="icon-sm text-light" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate">{{ $event->title }}</div>
                            <div class="text-xs text-light">{{ $event->starts_at?->format('M d, Y') }} · {{ $event->location }}</div>
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="calendar" :title="__('No upcoming events')" :message="__('School events will show here.')" />
                @endforelse
            </x-card>
        </div>

        <div class="grid grid-2 mt-4">
            <x-card :title="__('Homework')">
                @forelse ($homework as $assignment)
                    <div class="list-row">
                        <x-icon name="pencil" class="icon-sm text-light" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate">{{ $assignment->title }}</div>
                            <div class="text-xs text-light">{{ $assignment->classSubject?->subject?->name }} · due {{ $assignment->due_on?->format('M d, Y') }}</div>
                        </div>
                        <span class="badge badge-warning">{{ $assignment->due_on?->isToday() ? __('Due today') : __('Due') }}</span>
                    </div>
                @empty
                    <x-empty-state icon="pencil" :title="__('No homework')" :message="__('Published assignments will appear here.')" />
                @endforelse
                <a href="{{ route('cms.parent.homework') }}" class="btn btn-ghost btn-sm mt-2">{{ __('View all homework') }}</a>
            </x-card>

            <x-card :title="__('Recent results')">
                @forelse ($recentGrades as $grade)
                    <div class="list-row">
                        <x-icon name="award" class="icon-sm text-light" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate">{{ $grade->assessment?->title ?? $grade->examSubject?->subject?->name }}</div>
                            <div class="text-xs text-light">{{ $grade->assessment?->classSubject?->subject?->name ?? $grade->examSubject?->subject?->name }}</div>
                        </div>
                        <span class="badge badge-success">{{ $grade->marks_obtained ?? '—' }}</span>
                    </div>
                @empty
                    <x-empty-state icon="award" :title="__('No results yet')" :message="__('Published grades will appear here.')" />
                @endforelse
                <a href="{{ route('cms.parent.academics') }}" class="btn btn-ghost btn-sm mt-2">{{ __('View academics') }}</a>
            </x-card>
        </div>

        <div class="grid grid-2 mt-4">
            <x-card :title="__('Announcements')">
                @forelse ($announcements as $item)
                    <div class="list-row">
                        <x-icon name="newspaper" class="icon-sm text-light" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate">{{ $item->title }}</div>
                            <div class="text-xs text-light">{{ $item->published_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="newspaper" :title="__('No announcements')" :message="__('School news will appear here.')" />
                @endforelse
            </x-card>

        </div>

        <div class="mt-4">
            <x-card :title="__('Quick actions')">
                <div class="portal-actions">
                    <a href="{{ route('cms.parent.wards.show', $ward) }}" class="btn btn-ghost">
                        <x-icon name="user" class="icon-sm" />
                        {{ __('Child profile') }}
                    </a>
                    <a href="{{ route('cms.parent.absence-requests') }}" class="btn btn-ghost">
                        <x-icon name="clipboard-check" class="icon-sm" />
                        {{ __('Explain an absence') }}
                    </a>
                    <a href="{{ route('cms.parent.meetings') }}" class="btn btn-ghost">
                        <x-icon name="calendar" class="icon-sm" />
                        {{ __('Request a meeting') }}
                    </a>
                    <a href="{{ route('cms.parent.messages') }}" class="btn btn-ghost">
                        <x-icon name="mail" class="icon-sm" />
                        {{ __('Message a teacher') }}
                    </a>
                    <a href="{{ route('cms.parent.requests') }}" class="btn btn-ghost">
                        <x-icon name="inbox" class="icon-sm" />
                        {{ __('Service request') }}
                    </a>
                </div>
            </x-card>
        </div>
    @endif
</x-layouts.app>
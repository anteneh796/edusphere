<x-layouts.app :title="'Attendance · ' . $session->classRoom->name . ' · ' . $session->date->format('M j, Y')">

    @php
        $statusOptions = \App\Support\Enums\AttendanceStatus::cases();
        $classLabel = $session->classRoom->gradeLevel->name ?? '';
    @endphp

    <x-breadcrumb :items="[
        ['label' => 'Attendance', 'url' => route('attendance.index')],
        ['label' => $session->classRoom->name . ' · ' . $session->date->format('M j, Y')],
    ]" />

    <x-page-header :title="$session->classRoom->name"
        :description="($classLabel ? $classLabel . ' · ' : '') . 'daily attendance for ' . $session->date->format('D, M j, Y')">
        <span style="display:inline-flex; align-items:center; gap: var(--space-2); flex-wrap:wrap;">
            <x-badge :color="$session->status?->badgeColor()" :dot="true">{{ $session->status?->label() }}</x-badge>
            @if ($session->isOpen())
                <form method="POST" action="{{ route('attendance.close', $session) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <x-icon name="lock" class="icon-sm" />
                        Close session
                    </button>
                </form>
            @endif
            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                <x-icon name="chevron-left" class="icon-sm" />
                All sessions
            </a>
        </span>
    </x-page-header>

    <div class="grid" style="grid-template-columns: 1fr 300px; gap: var(--space-4); align-items:start;" x-data="attendanceBoard(@js($boardRows))">
        {{-- Marking board --}}
        <x-card data-offline-cache>
            <div class="flex" style="justify-content: space-between; align-items:center; margin-bottom: var(--space-3); flex-wrap:wrap; gap: var(--space-2);">
                <div>
                    <h3 class="h4" style="margin-bottom:2px;">Marking board</h3>
                    <p class="text-sm text-muted">Tap a status per student, then save. Works offline too.</p>
                </div>
                <div class="att-summary">
                    @foreach (\App\Support\Enums\AttendanceStatus::cases() as $status)
                        <span class="att-chip {{ $status->value }}">
                            {{ $status->short() }} · <b x-text="summary['{{ $status->value }}']">0</b>
                        </span>
                    @endforeach
                </div>
            </div>

            <form method="POST" action="{{ route('attendance.update', $session) }}" @submit.prevent="handleSubmit($event)">
                @csrf
                @method('PUT')

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Student no.</th>
                                <th>Status</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($boardRows as $i => $student)
                                <tr>
                                    <td class="text-sm text-muted" x-text="loopIndex({{ $i }})">0</td>
                                    <td class="text-sm" x-text="rows[{{ $i }}].name">{{ $student['name'] }}</td>
                                    <td>
                                        <span class="code-chip" x-text="rows[{{ $i }}].number">{{ $student['number'] }}</span>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:6px;">
                                            @foreach ($statusOptions as $option)
                                                <button type="button" class="att-status"
                                                    :class="rows[{{ $i }}].status === '{{ $option->value }}' ? 'is-active' : ''"
                                                    data-status="{{ $option->value }}"
                                                    @click="rows[{{ $i }}].status = '{{ $option->value }}'"
                                                    title="{{ $option->label() }}">{{ $option->short() }}</button>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-input att-note-input" placeholder="Note…"
                                            x-model="rows[{{ $i }}].note" />
                                        <input type="hidden" name="records[{{ $i }}][student_id]" :value="rows[{{ $i }}].studentId" />
                                        <input type="hidden" name="records[{{ $i }}][status]" :value="rows[{{ $i }}].status" />
                                        <input type="hidden" name="records[{{ $i }}][note]" :value="rows[{{ $i }}].note" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-empty-state icon="users" title="No students in this class" message="Enroll students into this class first." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer" style="display:flex; justify-content: space-between; align-items:center; flex-wrap:wrap; gap: var(--space-2);">
                    <div class="text-sm text-muted">
                        <template x-if="pending > 0">
                            <span><b x-text="pending"></b> record(s) waiting to sync.</span>
                        </template>
                        <template x-if="pending === 0 && offlineMode">
                            <span>Work offline — marks are saved on this device and sync automatically.</span>
                        </template>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            <x-icon name="check" class="icon-sm" />
                            <span x-show="!offlineMode">Save attendance</span>
                            <span x-show="offlineMode" x-cloak>Save offline</span>
                        </button>
                    </div>
                </div>
            </form>
        </x-card>

        {{-- Sidebar --}}
        <div style="display:flex; flex-direction:column; gap: var(--space-4);">
            <x-card>
                <h3 class="h4" style="margin-bottom: var(--space-2);">Today at a glance</h3>
                <div class="att-summary">
                    <span class="att-chip present">P · <b>{{ $summary['present'] }}</b></span>
                    <span class="att-chip late">L · <b>{{ $summary['late'] }}</b></span>
                    <span class="att-chip absent">A · <b>{{ $summary['absent'] }}</b></span>
                    <span class="att-chip excused">E · <b>{{ $summary['excused'] }}</b></span>
                </div>
                <dl class="detail-list mt-3">
                    <div>
                        <dt>Assigned class</dt>
                        <dd>{{ $session->classRoom->name }} · {{ $session->classRoom->gradeLevel->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Academic year</dt>
                        <dd>{{ $session->classRoom->academicYear->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Date</dt>
                        <dd>{{ $session->date->format('D, M j, Y') }}</dd>
                    </div>
                    <div>
                        <dt>Taken by</dt>
                        <dd>{{ $session->takenBy?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Opened</dt>
                        <dd>{{ $session->opened_at?->format('g:i A') ?? '—' }}</dd>
                    </div>
                </dl>
            </x-card>

            <x-card>
                <h3 class="h4" style="margin-bottom: var(--space-2);">Offline mode</h3>
                <p class="text-sm text-muted">
                    This board is cached for offline use. Marks made without a connection are queued on this
                    device and pushed to the server automatically when you are back online.
                </p>
                <div class="text-sm mt-2" style="display:flex; align-items:center; gap:6px; color: var(--color-text-muted);">
                    <span class="offline-queue-dot" :style="pending > 0 ? 'background:#16a34a;' : ''"></span>
                    <span x-text="pending > 0 ? pending + ' pending sync' : 'All synced'">All synced</span>
                </div>
            </x-card>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('attendanceBoard', (seedRows) => ({
                    rows: seedRows.map(r => ({
                        ...r,
                        studentId: r.student_id,
                        status: r.status || 'present',
                        note: r.note || '',
                        clientId: (crypto.randomUUID ? crypto.randomUUID() : String(Date.now()) + Math.random()),
                    })),
                    saving: false,
                    pending: 0,
                    offlineMode: false,

                    init() {
                        this.offlineMode = !navigator.onLine;
                        this.refreshPending();

                        window.addEventListener('network-change', (e) => {
                            this.offlineMode = !e.detail.online;
                            if (e.detail.online) {
                                this.refreshPending();
                            }
                        });
                    },

                    loopIndex(i) {
                        return i + 1;
                    },

                    get summary() {
                        const counts = { present: 0, absent: 0, late: 0, excused: 0 };
                        this.rows.forEach(r => {
                            if (counts[r.status] !== undefined) counts[r.status]++;
                        });
                        return counts;
                    },

                    refreshPending() {
                        window.EduOffline?.pendingCount?.().then(c => { this.pending = c; });
                    },

                    async handleSubmit(event) {
                        const marked = this.rows.filter(r => r.status);
                        const base = {
                            class_room_id: @js($session->class_room_id),
                            date: @js($session->date->format('Y-m-d')),
                        };

                        if (!this.offlineMode || window.EduOffline == null) {
                            event.target.submit();
                            return;
                        }

                        if (marked.length === 0) {
                            window.EduToast?.push('Choose a status for at least one student.', 'warning');
                            return;
                        }

                        this.saving = true;
                        const payload = marked.map(r => ({
                            client_id: r.clientId,
                            student_id: r.studentId,
                            status: r.status,
                            note: r.note || '',
                            ...base,
                        }));

                        await window.EduOffline.queueAttendance(payload);
                        this.saving = false;
                        this.refreshPending();
                        window.EduToast?.push(
                            'Marks stored on this device. They will sync when you are back online.',
                            'success',
                            'Attendance saved offline'
                        );
                    },
                }));
            });
        </script>
    @endpush
</x-layouts.app>
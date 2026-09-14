<x-layouts.app :title="'ID Card · '.$student->full_name">
    @php
        $cardEnrollment = $student->enrollments->first();
    @endphp
    <x-breadcrumb :items="[
        ['label' => 'Students', 'url' => route('students.index')],
        ['label' => $student->full_name, 'url' => route('students.show', $student)],
        ['label' => 'ID Card'],
    ]" />

    <div class="flex flex-col no-print" style="gap: var(--space-3); margin-bottom: var(--space-4);">
        <div class="card p-4 flex align-center" style="justify-content: space-between; gap: var(--space-3);">
            <div>
                <h2 class="h4" style="margin-bottom: 2px;">Student ID Card</h2>
                <p class="text-sm text-muted">Print this page to issue a paper ID card for {{ $student->full_name }}.</p>
            </div>
            <div class="flex gap-1">
                <a href="{{ route('students.show', $student) }}" class="btn btn-secondary">Back</a>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <x-icon name="printer" class="icon-sm" />
                    Print
                </button>
            </div>
        </div>
    </div>

    <div class="id-card-sheet">
        <div class="id-card-face">
            <div class="id-card-header">
                <div>
                    <div class="id-card-school">EduSphere International School</div>
                    <div class="id-card-sub">Student Identity Card</div>
                </div>
                <span class="code-chip">{{ $student->student_number }}</span>
            </div>

            <div class="id-card-body">
                <x-avatar :initials="$student->initials()" size="xl" />
                <div class="id-card-details">
                    <div class="id-card-name">{{ $student->full_name }}</div>
                    <div class="kv-list" style="margin-top: 10px;">
                        <div class="kv-pair"><span>Grade</span><strong>{{ $student->gradeLevel?->name ?? '—' }}</strong></div>
                        <div class="kv-pair"><span>Class</span><strong>{{ $student->classRoom?->name ?? '—' }}</strong></div>
                        <div class="kv-pair"><span>Academic Year</span><strong>@if($cardEnrollment){{ $cardEnrollment->academicYear?->name }}@else{{ $student->academicYear?->name ?? '—' }}@endif</strong></div>
                    </div>
                </div>
            </div>

            <div class="id-card-footer">
                <div class="id-card-qr">EDUSPHERE {{ str_pad(substr((string) $student->getKey(), 0, 8), 12, '0', STR_PAD_RIGHT) }} • {{ $student->student_number }}</div>
                <div class="id-card-sign">Valid {{ now()->format('Y') }} Academic Year</div>
            </div>
        </div>
    </div>
</x-layouts.app>
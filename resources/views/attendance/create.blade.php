<x-layouts.app :title="'Take Attendance'">
    <x-breadcrumb :items="[
        ['label' => 'Attendance', 'url' => route('attendance.index')],
        ['label' => 'Take attendance'],
    ]" />

    <x-page-header title="Take attendance" description="Pick a class and date to open a marking board for {{ $currentYear->name }}." />

    <x-card>
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf

            <div style="display:flex; flex-direction:column; gap: var(--space-4);">
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
                <div class="form-group">
                    <label class="form-label" for="class_room_id">Class</label>
                    <select id="class_room_id" name="class_room_id" class="form-select" required>
                        <option value="">Select a class…</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('class_room_id') === $class->id)>
                                {{ $class->name }} · {{ $class->gradeLevel->name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_room_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="date">Date</label>
                    <input id="date" type="date" name="date" class="form-input" value="{{ old('date', $today) }}" required />
                    @error('date')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="clipboard-check" class="icon-sm" />
                    Open marking board
                </button>
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
            </div>
        </form>
    </x-card>
</x-layouts.app>
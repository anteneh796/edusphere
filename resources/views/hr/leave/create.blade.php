<x-layouts.app :title="__('Request Leave')">

    <x-page-header :title="__('Request Leave')" :description="__('Submit a new leave request for review.')">
        <a href="{{ route('hr.leave.my') }}" class="btn btn-secondary">{{ __('Back to my leave') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ $employee ? route('hr.leave.store.for', $employee) : route('hr.leave.store') }}">
            @csrf
            <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                <x-select name="leave_type_id" :label="__('Leave type')" :options="$leaveTypes->pluck('name', 'id')" :value="old('leave_type_id')" placeholder="{{ __('Select type…') }}" required />
                <x-input name="days" type="number" label="{{ __('Number of days') }}" :value="old('days')" min="1" placeholder="e.g. 5" required :hint="__('Including weekends and holidays.')" />
                <x-input name="start_date" type="date" label="{{ __('Start date') }}" :value="old('start_date')" required />
                <x-input name="end_date" type="date" label="{{ __('End date') }}" :value="old('end_date')" required />
            </div>

            @if (auth()->user()->hasPermission('hr.edit'))
                <div class="form-group">
                    <label class="form-label" for="employee_select">{{ __('Employee') }}</label>
                    <select id="employee_select" name="employee_select" class="form-select" onchange="const id=this.value; if(id){ window.location = '{{ route('hr.leave.create') }}/' + id; }">
                        <option value="">{{ __('Self (linked profile)') }}</option>
                        @foreach ($employees as $option)
                            <option value="{{ $option->id }}" @selected($employee?->getKey() === $option->id)>{{ $option->full_name }} ({{ $option->employee_id }})</option>
                        @endforeach
                    </select>
                    @if ($employee)
                        <div class="form-hint">{{ __('Submitting on behalf of :name.', ['name' => $employee->full_name]) }}</div>
                    @endif
                </div>
            @endif

            <x-textarea name="reason" label="{{ __('Reason') }}" :rows="4" placeholder="{{ __('Purpose of the leave…') }}">{{ old('reason') }}</x-textarea>

            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.leave.my') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="send" class="icon-sm" />
                    {{ __('Submit request') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>
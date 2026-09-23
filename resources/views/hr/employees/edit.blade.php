<x-layouts.app :title="__('Edit Employee')">

    <x-page-header :title="__('Edit').': '.$employee->full_name" :description="__('ID: :id · Employee record.', ['id' => $employee->employee_id])">
        <a href="{{ route('hr.employees.show', $employee) }}" class="btn btn-secondary">{{ __('Back to profile') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.employees.update', $employee) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('hr.employees._form')
            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.employees.show', $employee) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Save changes') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>
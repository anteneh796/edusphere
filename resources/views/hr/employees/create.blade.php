<x-layouts.app :title="__('Add Employee')">

    <x-page-header :title="__('Add Employee')" :description="__('Create a permanent HR record for a new staff member.')">
        <a href="{{ route('hr.employees.index') }}" class="btn btn-secondary">{{ __('Back to directory') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.employees.store') }}" enctype="multipart/form-data">
            @csrf
            @include('hr.employees._form')
            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.employees.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Create employee') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>
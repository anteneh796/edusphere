<x-layouts.app :title="__('Add Department')">

    <x-page-header :title="__('Add Department')" :description="__('Department names must be unique and act as organisational units for positions and staff.')">
        <a href="{{ route('hr.departments.index') }}" class="btn btn-secondary">{{ __('Back to departments') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.departments.store') }}">
            @csrf
            @include('hr.departments._form')
            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.departments.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Create department') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>
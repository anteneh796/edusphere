<x-layouts.app :title="__('Add Position')">

    <x-page-header :title="__('Add Position')" :description="__('Create a job title used for categorizing staff.')">
        <a href="{{ route('hr.positions.index') }}" class="btn btn-secondary">{{ __('Back to positions') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.positions.store') }}">
            @csrf
            @include('hr.positions._form')
            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.positions.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Create position') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>
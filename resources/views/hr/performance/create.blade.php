<x-layouts.app :title="__('New Performance Review')">

    <x-page-header :title="__('New Performance Review')" :description="__('Evaluate staff across scoring categories on a 0–100 scale.')">
        <a href="{{ route('hr.performance.index') }}" class="btn btn-secondary">{{ __('Back to reviews') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.performance.store') }}">
            @csrf
            @include('hr.performance._form')
            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.performance.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Save review') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>
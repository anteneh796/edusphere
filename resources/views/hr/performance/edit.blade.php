<x-layouts.app :title="__('Edit Performance Review')">

    <x-page-header :title="__('Edit Performance Review')" :description="__(':period · :employee', ['period' => $review->period, 'employee' => $review->employee->full_name])">
        <a href="{{ route('hr.performance.show', $review) }}" class="btn btn-secondary">{{ __('Back to review') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.performance.update', $review) }}">
            @csrf
            @method('PUT')
            @include('hr.performance._form')
            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.performance.show', $review) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Save changes') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>